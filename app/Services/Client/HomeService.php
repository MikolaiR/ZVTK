<?php

namespace App\Services\Client;

use App\Enums\Statuses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Inertia\Inertia;
use Inertia\Response;

class HomeService
{

    public function index(): View|Response
    {
        $statuses = array_map(function ($s) {
            return [
                'value' => $s->value,
                'label' => $s->lable(),
                'background' => $s->backgroundImg(),
            ];
        }, Statuses::allowedFor(Auth::user()));

        if (config('features.client_blade_enabled')) {
            return view('client.home', [
                'statuses' => $statuses,
            ]);
        }

        return Inertia::render('Home', [
            'statuses' => $statuses,
        ]);
    }
}
