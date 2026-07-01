<?php

namespace Tests\Feature;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoLocationPeriod;
use App\Models\Parking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AutoInventoryExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('features.client_blade_enabled', true);
    }

    public function test_export_button_is_visible_only_for_parking_status(): void
    {
        $user = $this->createUserWithPermissions(['view_status_parking']);

        $response = $this->actingAs($user)->get('/autos?status=4');
        $response->assertOk();
        $response->assertSee('Выгрузить в Excel');

        $response = $this->actingAs($user)->get('/autos?status=1');
        $response->assertOk();
        $response->assertDontSee('Выгрузить в Excel');
    }

    public function test_export_returns_excel_for_parking_status(): void
    {
        $user = $this->createUserWithPermissions(['view_status_parking']);
        $parking = $this->createParking('ЗВТК мост', 'Пр-т Дзержинского 25А');

        $auto = $this->createAuto('Present Car', 'VIN-PRESENT-001', Statuses::Parking, '2023-01-01');
        $this->createParkingPeriod($auto, $parking, now()->subDay());

        $response = $this->actingAs($user)
            ->get("/autos/export?status=4&parking_id={$parking->id}");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertNotNull($contentDisposition);
        $this->assertStringStartsWith('attachment; filename="inventory_', $contentDisposition);

        $worksheet = $this->readWorksheet($response->streamedContent());

        $this->assertSame('ИНВЕНТАРИЗАЦИЯ ТС НАХОДЯЩИХСЯ НА СТОЯНКЕ', $worksheet->getCell('A1')->getValue());
        $this->assertStringContainsString('Пр-т Дзержинского 25А', (string) $worksheet->getCell('A2')->getValue());
        $this->assertSame('Объект инвентаризации', $worksheet->getCell('A4')->getValue());
        $this->assertSame('Present Car', $worksheet->getCell('A5')->getValue());
        $this->assertSame('VIN-PRESENT-001', $worksheet->getCell('B5')->getValue());
        $this->assertSame(2023, $worksheet->getCell('C5')->getValue());
        $this->assertSame('В наличии на стоянке ЗВТК мост', $worksheet->getCell('D5')->getValue());
    }

    public function test_export_marks_moved_away_auto_as_absent(): void
    {
        $user = $this->createUserWithPermissions(['view_status_parking']);
        $parkingOne = $this->createParking('Стоянка 1', 'Адрес 1');
        $parkingTwo = $this->createParking('Стоянка 2', 'Адрес 2');

        $auto = $this->createAuto('Moved Away Car', 'VIN-MOVED-002', Statuses::Parking, '2025-01-01');

        $this->createParkingPeriod($auto, $parkingOne, now()->subMonths(2), now()->subMonth());
        $this->createParkingPeriod($auto, $parkingTwo, now()->subMonth());

        $response = $this->actingAs($user)
            ->get("/autos/export?status=4&parking_id={$parkingOne->id}");

        $response->assertOk();

        $worksheet = $this->readWorksheet($response->streamedContent());

        $this->assertSame('Moved Away Car', $worksheet->getCell('A5')->getValue());
        $this->assertSame('Отсутствует', $worksheet->getCell('D5')->getValue());
        $this->assertSame(now()->subMonth()->format('d.m.Y'), $worksheet->getCell('E5')->getValue());
        $this->assertSame('Стоянка 2', $worksheet->getCell('F5')->getValue());
    }

    public function test_export_requires_parking_status(): void
    {
        $user = $this->createUserWithPermissions(['view_status_delivery']);

        $response = $this->actingAs($user)->get('/autos/export?status=1');

        $response->assertStatus(400);
    }

    public function test_export_applies_vin_filter(): void
    {
        $user = $this->createUserWithPermissions(['view_status_parking']);
        $parking = $this->createParking('ЗВТК мост', 'Адрес');

        $visible = $this->createAuto('Visible Car', 'VIN-MATCH-001', Statuses::Parking, '2023-01-01');
        $hidden = $this->createAuto('Hidden Car', 'VIN-HIDDEN-002', Statuses::Parking, '2023-01-01');

        $this->createParkingPeriod($visible, $parking, now()->subDay());
        $this->createParkingPeriod($hidden, $parking, now()->subDay());

        $response = $this->actingAs($user)
            ->get("/autos/export?status=4&parking_id={$parking->id}&vin=MATCH");

        $response->assertOk();

        $worksheet = $this->readWorksheet($response->streamedContent());

        $this->assertSame('Visible Car', $worksheet->getCell('A5')->getValue());
        $this->assertNull($worksheet->getCell('A6')->getValue());
    }

    /**
     * @param array<int, string> $permissions
     */
    private function createUserWithPermissions(array $permissions): User
    {
        $user = User::factory()->create(['company_id' => null]);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->givePermissionTo($permissions);

        return $user;
    }

    private function createParking(string $name, string $address): Parking
    {
        return Parking::withoutEvents(fn () => Parking::query()->create([
            'name' => $name,
            'address' => $address,
        ]));
    }

    private function createAuto(string $title, string $vin, Statuses $status, ?string $year = null): Auto
    {
        return Auto::withoutEvents(fn () => Auto::query()->create([
            'title' => $title,
            'vin' => $vin,
            'status' => $status,
            'year' => $year,
        ]));
    }

    private function createParkingPeriod(
        Auto $auto,
        Parking $parking,
        \DateTimeInterface $startedAt,
        ?\DateTimeInterface $endedAt = null
    ): AutoLocationPeriod {
        return AutoLocationPeriod::query()->create([
            'auto_id' => $auto->id,
            'location_type' => Parking::class,
            'location_id' => $parking->id,
            'status' => Statuses::Parking->value,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
        ]);
    }

    private function readWorksheet(string $content): \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        file_put_contents($tempFile, $content);

        $spreadsheet = IOFactory::load($tempFile);
        unlink($tempFile);

        return $spreadsheet->getActiveSheet();
    }
}
