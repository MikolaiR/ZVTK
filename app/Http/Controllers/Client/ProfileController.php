<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __invoke(Request $request): View|Response
    {
        $user = $request->user()->only('id', 'name', 'email', 'company_id');
        $company = $request->user()->company()->first(['id', 'name']);

        $viewData = [
            'user' => $user,
            'company' => $company,
        ];

        if (config('features.client_blade_enabled')) {
            return view('client.profile', $viewData);
        }

        return Inertia::render('Profile/Index', $viewData);
    }
}
