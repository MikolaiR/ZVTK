<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Autos\TransitionRequest;
use App\Models\Auto;
use App\Services\Autos\ParkingCostCalculator;
use App\Services\Autos\Transitions\AcceptAtParkingTransition;
use App\Services\Autos\Transitions\MoveToCustomsFromParkingTransition;
use App\Services\Autos\Transitions\MoveToCustomsTransition;
use App\Services\Autos\Transitions\MoveToOtherParkingTransition;
use App\Services\Autos\Transitions\MoveToParkingTransition;
use App\Services\Autos\Transitions\SellTransition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AutoTransitionController extends Controller
{
    public function store(TransitionRequest $request, Auto $auto): RedirectResponse
    {
        $user = $request->user();

        $action = (string) $request->input('action');
        $map = [
            'move_to_customs' => MoveToCustomsTransition::class,
            'move_to_parking' => MoveToParkingTransition::class,
            'accept_at_parking' => AcceptAtParkingTransition::class,
            'move_to_customs_from_parking' => MoveToCustomsFromParkingTransition::class,
            'move_to_other_parking' => MoveToOtherParkingTransition::class,
            'sell' => SellTransition::class,
        ];

        abort_if(! isset($map[$action]), 422, 'Unknown action');

        /** @var \App\Services\Autos\Transitions\AutoTransition $service */
        $service = app($map[$action]);
        $payload = $request->validated();
        $payload['photos'] = (array) ($request->file('photos') ?? []);
        $payload['videos'] = (array) ($request->file('videos') ?? []);
        $payload['documents'] = (array) ($request->file('documents') ?? []);
        $service->handle($auto, $payload, $user);

        return back()->with('success', 'Статус обновлен');
    }

    public function storageCost(Request $request, Auto $auto): JsonResponse
    {
        $calculator = app(ParkingCostCalculator::class);
        $result = $calculator->calculate($auto);

        return response()->json($result);
    }
}

