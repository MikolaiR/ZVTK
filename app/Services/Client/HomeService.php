<?php

namespace App\Services\Client;

use App\Enums\Statuses;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeService
{

    public function index()
    {
        $statuses = array_map(function ($s) {
            return [
                'value' => $s->value,
                'label' => $s->lable(),
                'background' => $s->backgroundImg(),
            ];
        }, Statuses::allowedFor(Auth::user()));

        return Inertia::render('Home', [
            'statuses' => $statuses,
        ]);
    }
}
