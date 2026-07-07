<?php

namespace App\Services;

use App\Models\CurrencyRate;
use App\Repositories\CurrencyRepository;
use Carbon\Carbon;

class CurrencyRateService
{
    private ?array $cachedRate = null;

    public function __construct(
        private readonly CurrencyRepository $repository,
    ) {
    }

    /**
     * Return the USD -> BYN rate for the given date.
     *
     * @return array{value: float, available: bool, date: string}
     */
    public function getUsdToBynRate(?Carbon $date = null): array
    {
        $date = $date ?? now();
        $dateString = $date->toDateString();

        if ($this->cachedRate !== null && $this->cachedRate['date'] === $dateString) {
            return $this->cachedRate;
        }

        $rate = CurrencyRate::getRate('USD', $date);
        $available = true;

        if ($rate === null) {
            $rate = $this->repository->usdToByn();
            $available = $rate > 0;
        }

        $this->cachedRate = [
            'value' => (float) $rate,
            'available' => $available,
            'date' => $dateString,
        ];

        return $this->cachedRate;
    }
}
