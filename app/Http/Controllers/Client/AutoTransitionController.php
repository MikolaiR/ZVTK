<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Autos\TransitionRequest;
use App\Models\Auto;
use App\Services\Autos\ParkingCostCalculator;
use App\Services\Autos\AutoActionsResolver;
use App\Services\CurrencyRateService;
use App\Services\Autos\Transitions\AcceptAtParkingTransition;
use App\Services\Autos\Transitions\MoveToCustomsFromParkingTransition;
use App\Services\Autos\Transitions\MoveToCustomsTransition;
use App\Services\Autos\Transitions\MoveToOtherParkingTransition;
use App\Services\Autos\Transitions\MoveToParkingTransition;
use App\Services\Autos\Transitions\SaveFilesTransition;
use App\Services\Autos\Transitions\SellTransition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AutoTransitionController extends Controller
{
    public function __construct(
        private readonly CurrencyRateService $currencyService,
    ) {
    }

    public function store(TransitionRequest $request, Auto $auto): RedirectResponse
    {
        $user = $request->user();

        $this->authorize('update', $auto);

        $action = (string) $request->input('action');
        $map = [
            'move_to_customs' => MoveToCustomsTransition::class,
            'move_to_parking' => MoveToParkingTransition::class,
            'accept_at_parking' => AcceptAtParkingTransition::class,
            'move_to_customs_from_parking' => MoveToCustomsFromParkingTransition::class,
            'move_to_other_parking' => MoveToOtherParkingTransition::class,
            'sell' => SellTransition::class,
            'save_files' => SaveFilesTransition::class,
        ];

        abort_if(! isset($map[$action]), 422, 'Unknown action');

        if (! app(AutoActionsResolver::class)->canPerformTransition($user, $auto, $action)) {
            abort(403);
        }

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

        $rate = $this->currencyService->getUsdToBynRate();
        $rateValue = $rate['available'] ? $rate['value'] : null;

        $result['rate'] = $rate;
        $result['total_cost_byn'] = $rateValue !== null
            ? round($result['total_cost'] * $rateValue, 2)
            : null;

        foreach ($result['per_parkings'] as &$parking) {
            $parking['total_cost_byn'] = $rateValue !== null
                ? round($parking['total_cost'] * $rateValue, 2)
                : null;
        }
        unset($parking);

        return response()->json($result);
    }
}

