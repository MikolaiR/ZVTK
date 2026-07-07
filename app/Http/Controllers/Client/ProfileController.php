<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->only('id', 'name', 'email', 'company_id');
        $company = $request->user()->company()->first(['id', 'name']);

        return view('client.profile', [
            'user' => $user,
            'company' => $company,
        ]);
    }
}
