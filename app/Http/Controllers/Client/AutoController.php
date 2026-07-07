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
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\AutoResource;
use App\Services\Autos\AutoInventoryExportService;
use App\Services\Autos\CreateAutoService;
use App\Services\Autos\AutoActionsResolver;
use App\Filters\Autos\AutoFilters;
use App\Enums\Statuses;
use App\Support\MediaLibrary\MediaUrl;
use Illuminate\Support\Carbon;

class AutoController extends Controller
{
    public function __construct(
        private readonly CreateAutoService $creator,
        private readonly AutoInventoryExportService $inventoryExporter,
    ) {
    }

    public function index(Request $request): View
    {
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        $filters = [
            'vin' => (string) $request->get('vin', ''),
            'status' => $request->get('status'),
            'parking_id' => $request->get('parking_id'),
        ];

        $query = Auto::query()
            ->select(['id', 'title', 'vin', 'status', 'year', 'color_id', 'departure_date'])
            ->with([
                'color:id,name_ru as name',
                'currentLocation.location',
            ])
            ->orderByRaw('departure_date IS NULL')
            ->orderBy('departure_date', $direction)
            ->orderBy('id', $direction);

        AutoFilters::apply($query, $filters);

        $autos = $query->paginate(20)->withQueryString();
        $parkings = Parking::query()->select('id', 'name')->orderBy('name')->get();

        $viewData = [
            'autos' => $autos->through(function (Auto $a) {
                $preview = $a->getFirstMedia('photos');
                $previewUrl = $preview ? MediaUrl::url($preview, 'thumb') : null;
                $statusLabel = Statuses::from($a->status->value)->lable();
                $locationName = $a->currentLocation?->location?->name
                    ?? $a->currentLocation?->location?->title;
                $parkingStartedAt = $a->currentLocation?->started_at;
                $parkingHighlight = null;

                if ($a->status === Statuses::Parking && $parkingStartedAt instanceof Carbon) {
                    if ($parkingStartedAt->copy()->addMonthsNoOverflow(5)->lte(now())) {
                        $parkingHighlight = 'danger';
                    } elseif ($parkingStartedAt->copy()->addMonthsNoOverflow(4)->lte(now())) {
                        $parkingHighlight = 'warning';
                    }
                }

                $statusDetailedLabel = $locationName
                    ? sprintf('%s %s', $statusLabel, $locationName)
                    : $statusLabel;

                $year = $a->year ? date('Y', strtotime((string) $a->year)) : null;
                $departureDate = $a->departure_date ? date('Y-m-d', strtotime((string) $a->departure_date)) : null;

                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'status' => $a->status->value,
                    'status_label' => $statusLabel,
                    'status_detailed_label' => $statusDetailedLabel,
                    'year' => $year,
                    'color_name' => $a->color?->name,
                    'departure_date' => $departureDate,
                    'parking_id' => $a->currentLocation?->location_id,
                    'parking_name' => $locationName,
                    'parking_highlight' => $parkingHighlight,
                    'preview_url' => $previewUrl,
                ];
            }),
            'filters' => [
                'vin' => $filters['vin'],
                'status' => $filters['status'],
                'parking_id' => $filters['parking_id'],
            ],
            'sort' => [
                'direction' => $direction,
            ],
            'parkings' => $parkings,
        ];

        return view('client.autos.index', $viewData);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        $filters = [
            'vin' => (string) $request->get('vin', ''),
            'status' => $request->get('status'),
            'parking_id' => $request->get('parking_id'),
            'direction' => $direction,
        ];

        try {
            return $this->inventoryExporter->export($filters);
        } catch (\InvalidArgumentException $e) {
            abort(400, $e->getMessage());
        }
    }

    public function show(Request $request, Auto $auto): View
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

        return view('client.autos.show', $viewData);
    }

    public function create(): View
    {
        $brands = AutoBrand::query()->select('id', 'name')->orderBy('name')->get();
        $colors = Color::query()->select('id', 'name', 'name_ru', 'hex_code')->orderBy('name')->get();

        $viewData = [
            'brands' => $brands,
            'colors' => $colors,
        ];

        return view('client.autos.create', $viewData);
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

