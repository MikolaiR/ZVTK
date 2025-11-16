<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Autos\CreateRequest;
use App\Models\Auto;
use App\Models\AutoBrand;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Parking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\AutoResource;
use App\Services\Autos\CreateAutoService;
use App\Filters\Autos\AutoFilters;
use App\Enums\Statuses;
use App\Support\MediaLibrary\MediaUrl;

class AutoController extends Controller
{
    public function __construct(private readonly CreateAutoService $creator)
    {
    }

    public function index(Request $request): Response
    {
        $filters = [
            'vin' => (string) $request->get('vin', ''),
            'status' => $request->get('status'),
        ];

        $query = Auto::query()
            ->select(['id', 'title', 'vin', 'status'])
            ->latest();

        AutoFilters::apply($query, $filters);

        $autos = $query->paginate(20)->withQueryString();

        return Inertia::render('Autos/Index', [
            'autos' => $autos->through(function (Auto $a) {
                $preview = $a->getFirstMedia('photos');
                $previewUrl = $preview ? MediaUrl::url($preview, 'thumb') : null;

                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'status' => $a->status->value,
                    'status_label' => Statuses::from($a->status->value)->lable(),
                    'preview_url' => $previewUrl,
                ];
            }),
            'filters' => [
                'vin' => $filters['vin'],
                'status' => $filters['status'],
            ],
        ]);
    }

    public function show(Request $request, Auto $auto): Response
    {
        $auto->load([
            'model:id,auto_brand_id,name',
            'model.brand:id,name',
            'color:id,name',
            'company:id,name',
            'sender:id,name',
            'provider:id,name,user_id',
            'provider.user:id,name',
            'currentLocation.location',
            'currentLocation.acceptedBy:id,name',
            'locationPeriods' => function ($q) {
                $q->orderByDesc('started_at')->with(['location', 'acceptedBy:id,name']);
            },
        ]);
        $resource = AutoResource::make($auto)->toArray($request);

        $parkings = Parking::query()->select('id', 'name')->orderBy('name')->get();

        $customers = Customer::query()->select('id', 'name')->orderBy('name')->get();
        return Inertia::render('Autos/Show', [
            'auto' => $resource,
            'parkings' => $parkings,
            'customers' => $customers,
        ]);
    }

    public function create(): Response
    {
        $brands = AutoBrand::query()->select('id', 'name')->orderBy('name')->get();
        $colors = Color::query()->select('id', 'name', 'name_ru', 'hex_code')->orderBy('name')->get();
        return Inertia::render('Autos/Create', [
            'brands' => $brands,
            'colors' => $colors,
        ]);
    }

    public function store(CreateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        try {
            $this->creator->handle(
                $data,
                $request->user(),
                [
                    'photos' => (array) ($request->file('photos') ?? []),
                    'videos' => (array) ($request->file('videos') ?? []),
                    'documents' => (array) ($request->file('documents') ?? []),
                ]
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
        return redirect()->route('autos.index')->with('success', 'Автомобиль добавлен');
    }
}

