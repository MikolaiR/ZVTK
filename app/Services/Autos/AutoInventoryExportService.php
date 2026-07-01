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
        'Наличие',
        'дата перемещения',
        'Перемещено в',
        'Сумма',
    ];

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
            ->select(['id', 'title', 'vin', 'status', 'year']);
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

        if ($parkingPeriod === null) {
            return [
                'title' => $auto->title,
                'vin' => $auto->vin,
                'year' => $this->formatYear($auto->year),
                'presence' => 'Нет данных по стоянке',
                'movement_date' => '',
                'destination' => '',
                'cost' => '',
            ];
        }

        if ($parkingPeriod->ended_at === null) {
            return [
                'title' => $auto->title,
                'vin' => $auto->vin,
                'year' => $this->formatYear($auto->year),
                'presence' => 'В наличии на стоянке ' . ($parkingPeriod->location?->name ?? ''),
                'movement_date' => '',
                'destination' => '',
                'cost' => $this->costService->calculateForPeriod($parkingPeriod),
            ];
        }

        $nextPeriod = $this->resolveNextPeriod($auto, $parkingPeriod);

        return [
            'title' => $auto->title,
            'vin' => $auto->vin,
            'year' => $this->formatYear($auto->year),
            'presence' => 'Отсутствует',
            'movement_date' => $parkingPeriod->ended_at->format('d.m.Y'),
            'destination' => $this->locationName($nextPeriod),
            'cost' => $this->costService->calculateForPeriod($parkingPeriod),
        ];
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
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', self::REPORT_TITLE);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        if ($parking) {
            $sheet->mergeCells('A2:G2');
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
            $sheet->setCellValue('D' . $currentRow, $row['presence']);
            $sheet->setCellValue('E' . $currentRow, $row['movement_date']);
            $sheet->setCellValue('F' . $currentRow, $row['destination']);
            if ($row['cost'] !== '') {
                $sheet->setCellValue('G' . $currentRow, $row['cost']);
                $sheet->getStyle('G' . $currentRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            }
        }
    }

    private function applyStyles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $headerRow, int $lastDataRow): void
    {
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count(self::HEADERS));
        $range = 'A' . $headerRow . ':' . $lastCol . $lastDataRow;
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range(1, count(self::HEADERS)) as $columnIndex) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
