<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Autos\CreateRequest;
use App\Models\Auto;
use App\Models\AutoBrand;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Parking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\AutoResource;
use App\Services\Autos\CreateAutoService;
use App\Services\Autos\AutoActionsResolver;
use App\Filters\Autos\AutoFilters;
use App\Enums\Statuses;
use App\Support\MediaLibrary\MediaUrl;

class AutoController extends Controller
{
    public function __construct(private readonly CreateAutoService $creator)
    {
    }

    public function index(Request $request): Response|View
    {
        $filters = [
            'vin' => (string) $request->get('vin', ''),
            'status' => $request->get('status'),
        ];

        $query = Auto::query()
            ->select(['id', 'title', 'vin', 'status', 'year', 'color_id'])
            ->with([
                'color:id,name_ru as name',
                'currentLocation.location',
            ])
            ->latest();

        AutoFilters::apply($query, $filters);

        $autos = $query->paginate(20)->withQueryString();

        $viewData = [
            'autos' => $autos->through(function (Auto $a) {
                $preview = $a->getFirstMedia('photos');
                $previewUrl = $preview ? MediaUrl::url($preview, 'thumb') : null;
                $statusLabel = Statuses::from($a->status->value)->lable();
                $locationName = $a->currentLocation?->location?->name
                    ?? $a->currentLocation?->location?->title;

                $statusDetailedLabel = $locationName
                    ? sprintf('%s %s', $statusLabel, $locationName)
                    : $statusLabel;

                $year = $a->year ? date('Y', strtotime((string) $a->year)) : null;

                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'status' => $a->status->value,
                    'status_label' => $statusLabel,
                    'status_detailed_label' => $statusDetailedLabel,
                    'year' => $year,
                    'color_name' => $a->color?->name,
                    'preview_url' => $previewUrl,
                ];
            }),
            'filters' => [
                'vin' => $filters['vin'],
                'status' => $filters['status'],
            ],
        ];

        if (config('features.client_blade_enabled')) {
            return view('client.autos.index', $viewData);
        }

        return Inertia::render('Autos/Index', $viewData);
    }

    public function show(Request $request, Auto $auto): Response|View
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

        $actions = $request->user() ? app(AutoActionsResolver::class)->resolve($request->user(), $auto) : [];
        $viewData = [
            'auto' => $resource,
            'parkings' => $parkings,
            'customers' => $customers,
            'actions' => $actions,
        ];

        if (config('features.client_blade_enabled')) {
            return view('client.autos.show', $viewData);
        }

        return Inertia::render('Autos/Show', $viewData);
    }

    public function create(): Response|View
    {
        $brands = AutoBrand::query()->select('id', 'name')->orderBy('name')->get();
        $colors = Color::query()->select('id', 'name', 'name_ru', 'hex_code')->orderBy('name')->get();

        $viewData = [
            'brands' => $brands,
            'colors' => $colors,
        ];

        if (config('features.client_blade_enabled')) {
            return view('client.autos.create', $viewData);
        }

        return Inertia::render('Autos/Create', $viewData);
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

