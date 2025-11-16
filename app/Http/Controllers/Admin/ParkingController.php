<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Parking\StoreParkingRequest;
use App\Http\Requests\Admin\Parking\UpdateParkingRequest;
use App\Http\Requests\Admin\Parking\Prices\StorePriceRequest;
use App\Http\Requests\Admin\Parking\Prices\UpdatePriceRequest;
use App\Models\Company;
use App\Models\Parking;
use App\Models\Price;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ParkingController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Parking::query()->with('company:id,name')->orderByDesc('id');
        $parkings = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Parkings/Index', [
            'parkings' => $parkings->through(function (Parking $p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'address' => $p->address,
                    'company' => $p->company ? [
                        'id' => $p->company->id,
                        'name' => $p->company->name,
                    ] : null,
                ];
            }),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Parkings/Create', [
            'companies' => Company::query()->select('id','name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreParkingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Parking::create($data);
        return redirect()->route('admin.parkings.index')->with('success', __('Parking created.'));
    }

    public function edit(Parking $parking): Response
    {
        $parking->load(['company:id,name', 'prices' => function ($q) { $q->orderByDesc('date_start'); }]);
        $prices = $parking->prices->map(function (Price $pr) {
            return [
                'id' => $pr->id,
                'name' => $pr->name,
                'price' => $pr->price,
                'date_start' => optional($pr->date_start)->toDateString(),
                'date_end' => optional($pr->date_end)->toDateString(),
            ];
        })->values();

        return Inertia::render('Admin/Parkings/Edit', [
            'parking' => [
                'id' => $parking->id,
                'name' => $parking->name,
                'address' => $parking->address,
                'company_id' => $parking->company?->id,
                'prices' => $prices,
            ],
            'companies' => Company::query()->select('id','name')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateParkingRequest $request, Parking $parking): RedirectResponse
    {
        $parking->update($request->validated());
        return redirect()->route('admin.parkings.index')->with('success', __('Parking updated.'));
    }

    public function destroy(Parking $parking): RedirectResponse
    {
        if (!$parking->trashed()) {
            $parking->delete();
        }
        return back()->with('success', __('Parking deleted.'));
    }

    public function storePrice(StorePriceRequest $request, Parking $parking): RedirectResponse
    {
        $data = $request->validated();
        $parking->prices()->create([
            'name' => $data['name'],
            'price' => $data['price'],
            'date_start' => $data['date_start'],
            'date_end' => $data['date_end'] ?? null,
        ]);
        return back()->with('success', __('Price created.'));
    }

    public function updatePrice(UpdatePriceRequest $request, Parking $parking, Price $price): RedirectResponse
    {
        abort_if($price->parking_id !== $parking->id, 404);
        $price->update($request->validated());
        return back()->with('success', __('Price updated.'));
    }

    public function destroyPrice(Parking $parking, Price $price): RedirectResponse
    {
        abort_if($price->parking_id !== $parking->id, 404);
        $price->delete();
        return back()->with('success', __('Price deleted.'));
    }
}
