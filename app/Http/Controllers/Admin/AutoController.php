<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Statuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auto\StoreAutoRequest;
use App\Http\Requests\Admin\Auto\UpdateAutoRequest;
use App\Http\Requests\Admin\Auto\Periods\StorePeriodRequest;
use App\Http\Requests\Admin\Auto\Periods\UpdatePeriodRequest;
use App\Models\Auto;
use App\Models\AutoBrand;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Parking;
use App\Models\AutoLocationPeriod;
use App\Models\Sender;
use App\Models\Company;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Support\MediaLibrary\MediaUrl;

class AutoController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Auto::class);

        $search = (string) $request->query('search', '');

        $query = Auto::query()
            ->with(['model.brand'])
            ->select(['id', 'title', 'vin', 'status'])
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('vin', 'like', "%{$search}%");
            });
        }

        $autos = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Autos/Index', [
            'filters' => ['search' => $search],
            'autos' => $autos->through(function (Auto $a) {
                $preview = $a->getFirstMedia('photos');
                $previewUrl = $preview ? MediaUrl::url($preview, 'thumb') : null;
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'vin' => $a->vin,
                    'status' => $a->status->value,
                    'status_label' => Statuses::from($a->status->value)->lable(),
                    'preview_url' => $previewUrl,
                ];
            }),
        ]);
    }

    public function create(): Response
    {
        // Reuse client create page props
        $brands = AutoBrand::query()->select('id', 'name')->orderBy('name')->get();
        $colors = Color::query()->select('id', 'name', 'name_ru', 'hex_code')->orderBy('name')->get();
        return Inertia::render('Autos/Create', [
            'brands' => $brands,
            'colors' => $colors,
        ]);
    }

    public function edit(Request $request, Auto $auto): Response
    {
        $this->authorize('view', $auto);

        $auto->load([
            'model:id,auto_brand_id,name',
            'model.brand:id,name',
            'color:id,name',
            'company:id,name',
            'sender:id,name',
            'provider:id,name',
            'locationPeriods' => function ($q) {
                $q->orderByDesc('started_at')->with(['location', 'acceptedBy:id,name']);
            },
        ]);

        $brands = AutoBrand::query()->select('id', 'name')->orderBy('name')->get();
        $colors = Color::query()->select('id', 'name', 'name_ru', 'hex_code')->orderBy('name')->get();

        $makeUrl = fn($m, $conv = '') => MediaUrl::url($m, $conv);

        $photos = $auto->getMedia('photos')->map(fn($m) => [
            'id' => $m->id,
            'url' => $makeUrl($m, 'preview'),
            'thumb_url' => $makeUrl($m, 'thumb'),
            'full_url' => $makeUrl($m, 'large'),
            'name' => $m->name,
            'file_name' => $m->file_name,
        ])->values();
        $videos = $auto->getMedia('videos')->map(fn($m) => [
            'id' => $m->id,
            'url' => $makeUrl($m),
            'name' => $m->name,
            'file_name' => $m->file_name,
        ])->values();
        $documents = $auto->getMedia('documents')->map(fn($m) => [
            'id' => $m->id,
            'url' => $makeUrl($m),
            'name' => $m->name,
            'file_name' => $m->file_name,
        ])->values();

        $statuses = collect(Statuses::cases())->map(fn($s) => ['value' => $s->value, 'label' => $s->lable()])->values();

        $mapTypeLabel = function (string $type) {
            $b = class_basename($type);
            return match ($b) {
                'Customer' => 'Таможня',
                'Parking' => 'Стоянка',
                'Provider' => 'Перевозчик',
                'Sender' => 'Отправитель',
                default => $b,
            };
        };

        $periods = $auto->locationPeriods->map(function ($p) use ($mapTypeLabel) {
            return [
                'id' => $p->id,
                'type' => $p->location_type,
                'type_label' => $mapTypeLabel($p->location_type),
                'name' => $p->location->name ?? ($p->location->title ?? null),
                'location_id' => $p->location?->id,
                'status' => $p->status,
                'status_label' => Statuses::from((int) $p->status)->lable(),
                'started_at' => optional($p->started_at)->toDateTimeString(),
                'ended_at' => optional($p->ended_at)->toDateTimeString(),
                'accepted_by' => $p->acceptedBy ? [
                    'id' => $p->acceptedBy->id,
                    'name' => $p->acceptedBy->name,
                ] : null,
                'acceptance_note' => $p->acceptance_note,
            ];
        })->values();

        return Inertia::render('Admin/Autos/Edit', [
            'auto' => [
                'id' => $auto->id,
                'title' => $auto->title,
                'vin' => $auto->vin,
                'auto_brand_id' => $auto->model?->auto_brand_id,
                'auto_model_id' => $auto->model?->id,
                'color_id' => $auto->color?->id,
                'company_id' => $auto->company?->id,
                'sender_id' => $auto->sender?->id,
                'provider_id' => $auto->provider?->id,
                'departure_date' => $auto->departure_date ? date('Y-m-d', strtotime((string) $auto->departure_date)) : null,
                'year' => $auto->year ? (int) date('Y', strtotime((string) $auto->year)) : null,
                'price' => $auto->price,
                'status' => $auto->status->value,
                'media' => [
                    'photos' => $photos,
                    'videos' => $videos,
                    'documents' => $documents,
                ],
                'periods' => $periods,
            ],
            'brands' => $brands,
            'colors' => $colors,
            'statuses' => $statuses,
            'customers' => Customer::query()->select('id','name')->orderBy('name')->get(),
            'parkings' => Parking::query()->select('id','name')->orderBy('name')->get(),
            'companies' => Company::query()->select('id','name')->orderBy('name')->get(),
            'senders' => Sender::query()->select('id','name')->orderBy('name')->get(),
            'providers' => Provider::query()->select('id','name')->orderBy('name')->get(),
            'users' => User::query()->select('id','name')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateAutoRequest $request, Auto $auto): RedirectResponse
    {
        $data = $request->validated();
        $data['photos'] = (array) ($request->file('photos') ?? []);
        $data['videos'] = (array) ($request->file('videos') ?? []);
        $data['documents'] = (array) ($request->file('documents') ?? []);

        app(\App\Services\Autos\UpdateAutoService::class)->handle($auto, $data);

        return redirect()->route('admin.autos.index')->with('success', __('Auto updated.'));
    }

    public function destroy(Auto $auto): RedirectResponse
    {
        if (!$auto->trashed()) {
            $auto->delete();
        }
        return back()->with('success', __('Auto deleted.'));
    }

    public function storePeriod(StorePeriodRequest $request, Auto $auto): RedirectResponse
    {
        $data = $request->validated();
        $status = (int) $data['status'];
        $locationType = Statuses::from($status)->connectionWithModel();
        $locationId = $data['location_id'] ?? null;
        if ($status === Statuses::Delivery->value) {
            $actor = $request->user();
            $sender = Sender::firstOrCreate(
                ['user_id' => $actor->id, 'company_id' => $actor->company_id],
                ['name' => $actor->name]
            );
            $locationId = $sender->id;
        }

        $auto->locationPeriods()->active()->update(['ended_at' => now()]);

        $auto->locationPeriods()->create([
            'location_type' => $locationType,
            'location_id' => $locationId,
            'started_at' => now(),
            'ended_at' => null,
            'accepted_by_user_id' => $data['accepted_by_user_id'] ?? $request->user()->id,
            'acceptance_note' => $data['note'] ?? null,
            'status' => $status,
        ]);

        $auto->update(['status' => $status]);

        return back()->with('success', __('Location period created.'));
    }

    public function closePeriod(Request $request, Auto $auto, AutoLocationPeriod $period): RedirectResponse
    {
        abort_if($period->auto_id !== $auto->id, 404);
        if (is_null($period->ended_at)) {
            $period->update(['ended_at' => now()]);
        }
        return back()->with('success', __('Location period closed.'));
    }

    public function destroyPeriod(Auto $auto, AutoLocationPeriod $period): RedirectResponse
    {
        abort_if($period->auto_id !== $auto->id, 404);
        $period->delete();
        return back()->with('success', __('Location period deleted.'));
    }

    public function updatePeriod(UpdatePeriodRequest $request, Auto $auto, AutoLocationPeriod $period): RedirectResponse
    {
        abort_if($period->auto_id !== $auto->id, 404);

        $data = $request->validated();
        $status = (int) $data['status'];

        $locationType = Statuses::from($status)->connectionWithModel();
        $locationId = $data['location_id'] ?? null;
        if ($status === Statuses::Delivery->value) {
            $actor = $request->user();
            $sender = Sender::firstOrCreate(
                ['user_id' => $actor->id, 'company_id' => $actor->company_id],
                ['name' => $actor->name]
            );
            $locationId = $sender->id;
        }

        $period->update([
            'location_type' => $locationType,
            'location_id' => $locationId,
            'status' => $status,
            'started_at' => $data['started_at'],
            'ended_at' => $data['ended_at'] ?? null,
            'acceptance_note' => $data['note'] ?? null,
            'accepted_by_user_id' => $data['accepted_by_user_id'] ?? $period->accepted_by_user_id,
        ]);

        if (empty($data['ended_at'])) {
            // Ensure this is the only active period
            $auto->locationPeriods()
                ->whereNull('ended_at')
                ->where('id', '!=', $period->id)
                ->update(['ended_at' => now()]);
            $auto->update(['status' => $status]);
        }

        return back()->with('success', __('Location period updated.'));
    }
}
