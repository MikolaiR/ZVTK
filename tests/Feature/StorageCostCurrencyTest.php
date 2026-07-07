<?php

namespace Tests\Feature;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoLocationPeriod;
use App\Models\CurrencyRate;
use App\Models\Parking;
use App\Models\Price;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StorageCostCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_storage_cost_uses_existing_rate_from_database(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 6, 1));

        $user = User::factory()->create();
        $parking = $this->createParking();

        Price::query()->create([
            'parking_id' => $parking->id,
            'name' => 'Тариф',
            'price' => 100,
            'date_start' => '2025-01-01',
            'date_end' => null,
        ]);

        CurrencyRate::query()->create([
            'currency_code' => 'USD',
            'rate' => 3.25,
            'rate_date' => '2025-06-01',
        ]);

        $auto = $this->createAuto($parking, Carbon::create(2025, 6, 1));

        $response = $this->actingAs($user)
            ->getJson("/autos/{$auto->id}/storage-cost");

        $response->assertOk();
        $response->assertJsonPath('rate.available', true);
        $response->assertJsonPath('rate.value', 3.25);
        $response->assertJsonPath('total_cost', 100);
        $response->assertJsonPath('total_cost_byn', 325.0);
        $response->assertJsonPath('per_parkings.0.total_cost_byn', 325.0);

        Carbon::setTestNow();
    }

    public function test_storage_cost_fetches_and_caches_rate_when_missing(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 6, 1));

        Http::fake([
            'https://www.nbrb.by/api/exrates/rates/431' => Http::response([
                'Cur_ID' => 431,
                'Cur_OfficialRate' => 3.25,
            ]),
        ]);

        $user = User::factory()->create();
        $parking = $this->createParking();

        Price::query()->create([
            'parking_id' => $parking->id,
            'name' => 'Тариф',
            'price' => 100,
            'date_start' => '2025-01-01',
            'date_end' => null,
        ]);

        $this->assertNull(CurrencyRate::getRate('USD', Carbon::today()));

        $auto = $this->createAuto($parking, Carbon::create(2025, 6, 1));

        $response = $this->actingAs($user)
            ->getJson("/autos/{$auto->id}/storage-cost");

        $response->assertOk();
        $response->assertJsonPath('rate.available', true);
        $response->assertJsonPath('rate.value', 3.25);
        $response->assertJsonPath('total_cost_byn', 325.0);

        $this->assertDatabaseHas('currency_rates', [
            'currency_code' => 'USD',
            'rate' => 3.25,
            'rate_date' => '2025-06-01',
        ]);

        Carbon::setTestNow();
        Http::fake();
    }

    public function test_storage_cost_shows_error_when_rate_unavailable(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 6, 1));

        Http::fake([
            'https://www.nbrb.by/api/exrates/rates/431' => Http::response([], 500),
        ]);

        $user = User::factory()->create();
        $parking = $this->createParking();

        Price::query()->create([
            'parking_id' => $parking->id,
            'name' => 'Тариф',
            'price' => 100,
            'date_start' => '2025-01-01',
            'date_end' => null,
        ]);

        $auto = $this->createAuto($parking, Carbon::create(2025, 6, 1));

        $response = $this->actingAs($user)
            ->getJson("/autos/{$auto->id}/storage-cost");

        $response->assertOk();
        $response->assertJsonPath('rate.available', false);
        $response->assertJsonPath('total_cost', 100);
        $response->assertJsonPath('total_cost_byn', null);

        Carbon::setTestNow();
        Http::fake();
    }

    private function createParking(): Parking
    {
        return Parking::withoutEvents(fn () => Parking::query()->create([
            'name' => 'Тестовая стоянка',
            'address' => 'Тестовый адрес',
        ]));
    }

    private function createAuto(Parking $parking, Carbon $startedAt): Auto
    {
        $auto = Auto::withoutEvents(fn () => Auto::query()->create([
            'title' => 'Test Car',
            'vin' => 'VIN-TEST-001',
            'status' => Statuses::Parking,
        ]));

        AutoLocationPeriod::query()->create([
            'auto_id' => $auto->id,
            'location_type' => Parking::class,
            'location_id' => $parking->id,
            'status' => Statuses::Parking->value,
            'started_at' => $startedAt,
            'ended_at' => null,
        ]);

        return $auto;
    }
}
