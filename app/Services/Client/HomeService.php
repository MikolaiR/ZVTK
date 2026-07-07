<?php

namespace App\Services\Client;

use App\Enums\Statuses;
use Illuminate\Support\Facades\Auth;


class HomeService
{

    public function index(): array
    {
        $statuses = array_map(function ($s) {
            return [
                'value' => $s->value,
                'label' => $s->lable(),
                'background' => $s->backgroundImg(),
            ];
        }, Statuses::allowedFor(Auth::user()));

        return $statuses;
    }
}
