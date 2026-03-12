<?php

namespace Tests\Feature;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoLocationPeriod;
use App\Models\Color;
use App\Models\Parking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ClientBladePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('features.client_blade_enabled', true);
    }

    public function test_authenticated_user_can_open_blade_home_page(): void
    {
        $user = $this->createUserWithPermissions(['view_status_delivery']);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertViewIs('client.home');
        $response->assertSee('Статусы автомобилей');
    }

    public function test_autos_index_uses_blade_view_and_applies_vin_filter(): void
    {
        $user = $this->createUserWithPermissions(['view_status_delivery']);

        Auto::withoutEvents(function () {
            Auto::query()->create([
                'title' => 'Auto Keep',
                'vin' => 'VIN-KEEP-001',
                'status' => Statuses::Delivery,
            ]);

            Auto::query()->create([
                'title' => 'Auto Hide',
                'vin' => 'VIN-HIDE-002',
                'status' => Statuses::Delivery,
            ]);
        });

        $response = $this->actingAs($user)->get('/autos?vin=KEEP');

        $response->assertOk();
        $response->assertViewIs('client.autos.index');
        $response->assertSee('Auto Keep');
        $response->assertDontSee('Auto Hide');
    }

    public function test_autos_index_displays_year_color_and_detailed_status_with_current_location(): void
    {
        $user = $this->createUserWithPermissions(['view_status_parking']);

        $color = Color::query()->create([
            'name' => 'Белый',
            'name_ru' => 'Белый',
            'hex_code' => '#FFFFFF',
        ]);

        $auto = Auto::withoutEvents(fn () => Auto::query()->create([
            'title' => 'Parking Car',
            'vin' => 'VIN-PARKING-INDEX-300',
            'status' => Statuses::Parking,
            'year' => '2021-01-01',
            'color_id' => $color->id,
        ]));

        $parking = Parking::withoutEvents(fn () => Parking::query()->create([
            'name' => 'ЗВТК мост',
            'address' => 'Мост 1',
        ]));

        AutoLocationPeriod::query()->create([
            'auto_id' => $auto->id,
            'location_type' => Parking::class,
            'location_id' => $parking->id,
            'status' => Statuses::Parking->value,
            'started_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get('/autos');

        $response->assertOk();
        $response->assertViewIs('client.autos.index');
        $response->assertSee('Parking Car');
        $response->assertSee('2021 • Белый');
        $response->assertSee('На стоянке ЗВТК мост');
    }

    public function test_autos_index_sorts_by_departure_date_ascending_by_default(): void
    {
        $user = $this->createUserWithPermissions(['view_status_delivery']);

        Auto::withoutEvents(function () {
            Auto::query()->create([
                'title' => 'Older Departure',
                'vin' => 'VIN-OLD-001',
                'status' => Statuses::Delivery,
                'departure_date' => '2025-01-10',
            ]);

            Auto::query()->create([
                'title' => 'Newer Departure',
                'vin' => 'VIN-NEW-002',
                'status' => Statuses::Delivery,
                'departure_date' => '2025-02-10',
            ]);
        });

        $response = $this->actingAs($user)->get('/autos');

        $response->assertOk();
        $response->assertSeeInOrder(['Older Departure', 'Newer Departure'], false);
        $response->assertSee('2025-01-10');
        $response->assertSee('2025-02-10');
        $response->assertSee('>↑<', false);
    }

    public function test_autos_index_can_sort_by_departure_date_descending(): void
    {
        $user = $this->createUserWithPermissions(['view_status_delivery']);

        Auto::withoutEvents(function () {
            Auto::query()->create([
                'title' => 'Older Departure',
                'vin' => 'VIN-OLD-101',
                'status' => Statuses::Delivery,
                'departure_date' => '2025-01-10',
            ]);

            Auto::query()->create([
                'title' => 'Newer Departure',
                'vin' => 'VIN-NEW-202',
                'status' => Statuses::Delivery,
                'departure_date' => '2025-02-10',
            ]);
        });

        $response = $this->actingAs($user)->get('/autos?direction=desc');

        $response->assertOk();
        $response->assertSeeInOrder(['Newer Departure', 'Older Departure'], false);
        $response->assertSee('>↓<', false);
    }

    public function test_autos_index_filters_parking_status_by_current_parking_only(): void
    {
        $user = $this->createUserWithPermissions(['view_status_parking']);

        $parkingOne = Parking::withoutEvents(fn () => Parking::query()->create([
            'name' => 'Стоянка 1',
            'address' => 'Адрес 1',
        ]));

        $parkingTwo = Parking::withoutEvents(fn () => Parking::query()->create([
            'name' => 'Стоянка 2',
            'address' => 'Адрес 2',
        ]));

        $autoAtParkingOne = Auto::withoutEvents(fn () => Auto::query()->create([
            'title' => 'Parking One Car',
            'vin' => 'VIN-PARK-ONE',
            'status' => Statuses::Parking,
        ]));

        $autoMovedAway = Auto::withoutEvents(fn () => Auto::query()->create([
            'title' => 'Moved Away Car',
            'vin' => 'VIN-MOVED-AWAY',
            'status' => Statuses::Parking,
        ]));

        AutoLocationPeriod::query()->create([
            'auto_id' => $autoAtParkingOne->id,
            'location_type' => Parking::class,
            'location_id' => $parkingOne->id,
            'status' => Statuses::Parking->value,
            'started_at' => now()->subMonth(),
        ]);

        AutoLocationPeriod::query()->create([
            'auto_id' => $autoMovedAway->id,
            'location_type' => Parking::class,
            'location_id' => $parkingOne->id,
            'status' => Statuses::Parking->value,
            'started_at' => now()->subMonths(2),
            'ended_at' => now()->subMonth(),
        ]);

        AutoLocationPeriod::query()->create([
            'auto_id' => $autoMovedAway->id,
            'location_type' => Parking::class,
            'location_id' => $parkingTwo->id,
            'status' => Statuses::Parking->value,
            'started_at' => now()->subMonth(),
        ]);

        $response = $this->actingAs($user)->get("/autos?status=4&parking_id={$parkingOne->id}");

        $response->assertOk();
        $response->assertSee('Parking One Car');
        $response->assertDontSee('Moved Away Car');
    }

    public function test_autos_index_marks_parking_rows_with_warning_and_danger_highlights(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 12, 12, 0, 0));

        $user = $this->createUserWithPermissions(['view_status_parking']);

        $parking = Parking::withoutEvents(fn () => Parking::query()->create([
            'name' => 'Длительная стоянка',
            'address' => 'Адрес 3',
        ]));

        $warningAuto = Auto::withoutEvents(fn () => Auto::query()->create([
            'title' => 'Warning Parking Car',
            'vin' => 'VIN-WARNING-001',
            'status' => Statuses::Parking,
        ]));

        $dangerAuto = Auto::withoutEvents(fn () => Auto::query()->create([
            'title' => 'Danger Parking Car',
            'vin' => 'VIN-DANGER-002',
            'status' => Statuses::Parking,
        ]));

        AutoLocationPeriod::query()->create([
            'auto_id' => $warningAuto->id,
            'location_type' => Parking::class,
            'location_id' => $parking->id,
            'status' => Statuses::Parking->value,
            'started_at' => now()->subMonthsNoOverflow(4),
        ]);

        AutoLocationPeriod::query()->create([
            'auto_id' => $dangerAuto->id,
            'location_type' => Parking::class,
            'location_id' => $parking->id,
            'status' => Statuses::Parking->value,
            'started_at' => now()->subMonthsNoOverflow(5),
        ]);

        $response = $this->actingAs($user)->get('/autos?status=4');

        $response->assertOk();
        $response->assertSeeInOrder(['data-parking-highlight="warning"', 'Warning Parking Car'], false);
        $response->assertSeeInOrder(['data-parking-highlight="danger"', 'Danger Parking Car'], false);

        Carbon::setTestNow();
    }

    public function test_autos_create_requires_create_auto_permission(): void
    {
        $user = $this->createUserWithPermissions(['view_status_delivery']);

        $response = $this->actingAs($user)->get('/autos/create');

        $response->assertForbidden();
    }

    public function test_autos_create_page_and_validation_work_in_blade_mode(): void
    {
        $user = $this->createUserWithPermissions(['view_status_delivery', 'create_auto']);

        $page = $this->actingAs($user)->get('/autos/create');
        $page->assertOk();
        $page->assertViewIs('client.autos.create');

        $store = $this->actingAs($user)->post('/autos', []);
        $store->assertSessionHasErrors(['auto_brand_id', 'auto_model_id', 'vin']);
    }

    public function test_autos_show_uses_blade_view(): void
    {
        $user = $this->createUserWithPermissions(['view_status_delivery']);

        $auto = Auto::withoutEvents(fn () => Auto::query()->create([
            'title' => 'Show Car',
            'vin' => 'VIN-SHOW-100',
            'status' => Statuses::Delivery,
        ]));

        $response = $this->actingAs($user)->get("/autos/{$auto->id}");

        $response->assertOk();
        $response->assertViewIs('client.autos.show');
        $response->assertSee('Show Car');
    }

    public function test_autos_show_displays_status_with_current_location_and_collapsible_sections_collapsed_by_default(): void
    {
        $user = $this->createUserWithPermissions(['view_status_parking']);

        $auto = Auto::withoutEvents(fn () => Auto::query()->create([
            'title' => 'Parking Car',
            'vin' => 'VIN-PARKING-200',
            'status' => Statuses::Parking,
        ]));

        $parking = Parking::withoutEvents(fn () => Parking::query()->create([
            'name' => 'ЗВТК мост',
            'address' => 'Мост 1',
        ]));

        AutoLocationPeriod::query()->create([
            'auto_id' => $auto->id,
            'location_type' => Parking::class,
            'location_id' => $parking->id,
            'status' => Statuses::Parking->value,
            'started_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get("/autos/{$auto->id}");

        $response->assertOk();
        $response->assertSee('На стоянке ЗВТК мост');
        $response->assertSee('<details data-section="status" class="client-card p-4" open>', false);
        $response->assertSee('<details data-section="actions" class="client-card p-4" open>', false);
        $response->assertSee('<details data-section="media" class="client-card p-4" open>', false);
        $response->assertSee('data-section="info"', false);
        $response->assertSee('data-section="history"', false);
        $response->assertDontSee('<details data-section="info" class="client-card p-4" open>', false);
        $response->assertDontSee('<details data-section="history" class="client-card p-4" open>', false);
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
}
