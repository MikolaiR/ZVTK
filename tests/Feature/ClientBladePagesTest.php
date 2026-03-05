<?php

namespace Tests\Feature;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoLocationPeriod;
use App\Models\Parking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
