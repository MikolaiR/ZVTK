<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\HomeService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly HomeService $homeService)
    {
    }

    public function __invoke(): View
    {
        $statuses = $this->homeService->index();

        return view('client.home', [
            'statuses' => $statuses,
        ]);
    }
}
