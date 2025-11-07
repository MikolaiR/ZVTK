<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user()->only('id', 'name', 'email', 'company_id');
        $company = $request->user()->company()->first(['id', 'name']);
        return Inertia::render('Profile/Index', [
            'user' => $user,
            'company' => $company,
        ]);
    }
}
