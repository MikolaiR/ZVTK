<?php

namespace App\Services\Autos;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoLocationPeriod;
use App\Models\Parking;
use App\Services\ParkingCostService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AutoInventoryExportService
{
    public function __construct(
        private readonly ParkingCostService $costService,
    ) {
    }

    private const SHEET_TITLE = 'Инвентаризация';
    private const REPORT_TITLE = 'ИНВЕНТАРИЗАЦИЯ ТС НАХОДЯЩИХСЯ НА СТОЯНКЕ';
    private const HEADERS = [
        'Объект инвентаризации',
        'VIN',
        'Г.В.',
        'Дата прибытия',
        'Наличие',
        'дата перемещения',
        'Перемещено в',
        'Сумма',
        'Фото',
    ];

    private const PHOTO_COL = 'I';
    private const PHOTO_HEIGHT = 80;

    public function export(array $filters): StreamedResponse
    {
        if ((int) ($filters['status'] ?? 0) !== Statuses::Parking->value) {
            throw new \InvalidArgumentException(
                'Экспорт инвентаризации доступен только для статуса «Стоянка».'
            );
        }

        $parkingId = !empty($filters['parking_id']) ? (int) $filters['parking_id'] : null;
        $parking = $parkingId ? Parking::find($parkingId) : null;
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $query = $this->buildQuery($parkingId, $direction);
        $this->applyVinFilter($query, (string) ($filters['vin'] ?? ''));

        $rows = $query->get()->map(fn (Auto $auto) => $this->buildRow($auto, $parking));

        $spreadsheet = $this->createSpreadsheet($parking, $rows);
        $filename = 'inventory_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return new StreamedResponse(
            function () use ($spreadsheet): void {
                (new Xlsx($spreadsheet))->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    private function buildQuery(?int $parkingId, string $direction): Builder
    {
        $query = Auto::query()
            ->select(['id', 'title', 'vin', 'status', 'year'])
            ->with('media');
        if (!$parkingId) {
            $query->where('status', Statuses::Parking->value);
        }
        if ($parkingId) {
            $query->whereHas('locationPeriods', function (Builder $locationQuery) use ($parkingId): void {
                $locationQuery
                    ->where('status', Statuses::Parking->value)
                    ->where('location_id', $parkingId);
            });
        }

        return $query->orderBy('title', $direction);
    }

    private function applyVinFilter(Builder $query, string $vin): void
    {
        $vin = trim($vin);
        if ($vin === '') {
            return;
        }

        $query->where('vin', 'like', "%{$vin}%");
    }

    private function buildRow(Auto $auto, ?Parking $parking): array
    {
        $parkingPeriod = $this->resolveParkingPeriod($auto, $parking);

        $photoPath = $this->resolvePhotoPath($auto);

        if ($parkingPeriod === null) {
            return [
                'title' => $auto->title,
                'vin' => $auto->vin,
                'year' => $this->formatYear($auto->year),
                'presence' => 'Нет данных по стоянке',
                'arrival_date' => '',
                'movement_date' => '',
                'destination' => '',
                'cost' => '',
                'photo_path' => $photoPath,
            ];
        }

        if ($parkingPeriod->ended_at === null) {
            return [
                'title' => $auto->title,
                'vin' => $auto->vin,
                'year' => $this->formatYear($auto->year),
                'presence' => 'В наличии на стоянке ' . ($parkingPeriod->location?->name ?? ''),
                'arrival_date' => $parkingPeriod->started_at->format('d.m.Y'),
                'movement_date' => '',
                'destination' => '',
                'cost' => $this->costService->calculateForPeriod($parkingPeriod),
                'photo_path' => $photoPath,
            ];
        }

        $nextPeriod = $this->resolveNextPeriod($auto, $parkingPeriod);

        return [
            'title' => $auto->title,
            'vin' => $auto->vin,
            'year' => $this->formatYear($auto->year),
            'presence' => 'Отсутствует',
            'arrival_date' => $parkingPeriod->started_at->format('d.m.Y'),
            'movement_date' => $parkingPeriod->ended_at->format('d.m.Y'),
            'destination' => $this->locationName($nextPeriod),
            'cost' => $this->costService->calculateForPeriod($parkingPeriod),
            'photo_path' => $photoPath,
        ];
    }

    private function resolvePhotoPath(Auto $auto): ?string
    {
        $media = $auto->getFirstMedia('photos');
        if ($media === null) {
            return null;
        }

        $path = $media->getPath();

        return (is_file($path) && is_readable($path)) ? $path : null;
    }

    private function resolveParkingPeriod(Auto $auto, ?Parking $parking): ?AutoLocationPeriod
    {
        $query = $auto->locationPeriods()
            ->where('status', Statuses::Parking->value)
            ->orderByDesc('started_at');

        if ($parking) {
            $query->where('location_id', $parking->id);
        } else {
            $query->whereNull('ended_at');
        }

        return $query->with('location')->first();
    }

    private function resolveNextPeriod(Auto $auto, AutoLocationPeriod $closedPeriod): ?AutoLocationPeriod
    {
        return $auto->locationPeriods()
            ->where('started_at', '>=', $closedPeriod->ended_at)
            ->where('id', '!=', $closedPeriod->id)
            ->orderBy('started_at')
            ->with('location')
            ->first();
    }

    private function locationName(?AutoLocationPeriod $period): string
    {
        if ($period === null || $period->location === null) {
            return '';
        }

        $location = $period->location;

        return $location->name
            ?? $location->title
            ?? $this->saleFallbackName($location);
    }

    private function saleFallbackName(object $location): string
    {
        if (class_basename(get_class($location)) === 'AutoSale') {
            return 'Продано';
        }

        return '';
    }

    private function formatYear(?string $year): string
    {
        if ($year === null) {
            return '';
        }

        return date('Y', strtotime($year));
    }

    private function createSpreadsheet(?Parking $parking, Collection $rows): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(self::SHEET_TITLE);

        $headerRow = $this->writeReportHeader($sheet, $parking);
        $this->writeColumnHeaders($sheet, $headerRow);
        $this->writeDataRows($sheet, $headerRow + 1, $rows);
        $this->applyStyles($sheet, $headerRow, $headerRow + $rows->count());

        return $spreadsheet;
    }

    private function writeReportHeader(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, ?Parking $parking): int
    {
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', self::REPORT_TITLE);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        if ($parking) {
            $sheet->mergeCells('A2:I2');
            $sheet->setCellValue('A2', 'Местонахождение стоянки: ' . $parking->address);
            $sheet->getStyle('A2')->getFont()->setBold(true);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row = 3;
        }

        return $row + 1;
    }

    private function writeColumnHeaders(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $row): void
    {
        foreach (self::HEADERS as $index => $header) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $cell = $column . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        }
    }

    private function writeDataRows(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $startRow, Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $currentRow = $startRow + $index;
            $sheet->setCellValue('A' . $currentRow, $row['title']);
            $sheet->setCellValue('B' . $currentRow, $row['vin']);
            $sheet->setCellValue('C' . $currentRow, $row['year']);
            $sheet->setCellValue('D' . $currentRow, $row['arrival_date']);
            $sheet->setCellValue('E' . $currentRow, $row['presence']);
            $sheet->setCellValue('F' . $currentRow, $row['movement_date']);
            $sheet->setCellValue('G' . $currentRow, $row['destination']);
            if ($row['cost'] !== '') {
                $sheet->setCellValue('H' . $currentRow, $row['cost']);
                $sheet->getStyle('H' . $currentRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            }
            if (!empty($row['photo_path'])) {
                $this->insertPhoto($sheet, $row['photo_path'], self::PHOTO_COL . $currentRow);
                $sheet->getRowDimension($currentRow)->setRowHeight(self::PHOTO_HEIGHT);
            }
        }
    }

    private function insertPhoto(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        string $path,
        string $cell
    ): void {
        $drawing = new Drawing();
        $drawing->setPath($path);
        $drawing->setCoordinates($cell);
        $drawing->setHeight(self::PHOTO_HEIGHT);
        $drawing->setOffsetX(2);
        $drawing->setOffsetY(2);
        $drawing->setWorksheet($sheet);
    }

    private function applyStyles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $headerRow, int $lastDataRow): void
    {
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count(self::HEADERS));
        $range = 'A' . $headerRow . ':' . $lastCol . $lastDataRow;
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range(1, count(self::HEADERS)) as $columnIndex) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            if ($column === self::PHOTO_COL) {
                $sheet->getColumnDimension($column)->setWidth(14);
            } else {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }
    }
}
