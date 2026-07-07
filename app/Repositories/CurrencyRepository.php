<?php

namespace App\Repositories;

use App\Models\CurrencyRate;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CurrencyRepository
{
    public function usdToByn(): float
    {
        return $this->fetchRate('USD', 431);
    }

    public function eurToByn(): float
    {
        return $this->fetchRate('EUR', 451);
    }

    public function rubToByn(): float
    {
        return $this->fetchRate('RUB', 456);
    }

    private function fetchRate(string $currencyCode, int $apiId): float
    {
        try {
            $url = "https://www.nbrb.by/api/exrates/rates/{$apiId}";
            $response = Http::timeout(5)
                ->retry(2, 200, throw: false)
                ->get($url);

            if (! $response->successful()) {
                return 0;
            }

            $data = $response->json();
            $rate = (float) ($data['Cur_OfficialRate'] ?? 0);

            if ($rate <= 0) {
                return 0;
            }

            CurrencyRate::query()->updateOrCreate(
                [
                    'currency_code' => $currencyCode,
                    'rate_date' => Carbon::today(),
                ],
                ['rate' => $rate]
            );

            return $rate;
        } catch (ConnectionException $exception) {
            return 0;
        } catch (\Throwable $exception) {
            report($exception);

            return 0;
        }
    }
}
