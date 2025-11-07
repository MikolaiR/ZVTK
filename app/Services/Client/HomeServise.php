<?php

namespace App\Services\Client;

use App\Enums\Statuses;
use Illuminate\Http\Client\Request;
use Inertia\Inertia;

class HomeServise
{

    public function index()
    {
        $statuses = array_map(function ($s) {
            return [
                'value' => $s->value,
                'label' => $s->lable(),
                'background' => $s->backgroundImg(),
            ];
        }, Statuses::cases());

        return Inertia::render('Home', [
            'statuses' => $statuses,
        ]);
    }
}
