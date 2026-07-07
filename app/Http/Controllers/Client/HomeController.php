<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\HomeService;
use Illuminate\Contracts\View\View;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{

public function __construct(private readonly HomeService $homeServic )
{
    
}
   
public function __invoke(): View|Response
{
    $statuses = $this->homeServic->index();
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
