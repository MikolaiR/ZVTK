<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoBrand;
use App\Models\AutoModel;
use App\Models\Color;
use App\Models\Provider;
use App\Models\Sender;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AutoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $vin = (string) $request->get('vin', '');
        $status = $request->integer('status');

        $query = Auto::query();
        if (! $user->hasRole('admin')) {
            $query->where('company_id', $user->company_id);
        }
        if ($vin !== '') {
            $query->where('vin', 'like', "%{$vin}%");
        }
        if ($status) {
            $query->where('status', $status);
        }

        $autos = $query
            ->select(['id', 'title', 'vin'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Autos/Index', [
            'autos' => $autos,
            'filters' => [
                'vin' => $vin,
                'status' => $status,
            ],
        ]);
    }

    public function show(Request $request, Auto $auto)
    {
        $user = $request->user();
        if (! $user->hasRole('admin') && (int) $auto->company_id !== (int) $user->company_id) {
            abort(404);
        }

        $auto->load([
            'autoModel:id,auto_brand_id,name',
            'autoModel.brand:id,name',
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

        $photos = $auto->getMedia('photos')->map(function ($m) {
            return [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'name' => $m->name,
                'file_name' => $m->file_name,
            ];
        })->values();
        $videos = $auto->getMedia('videos')->map(function ($m) {
            return [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'name' => $m->name,
                'file_name' => $m->file_name,
            ];
        })->values();
        $documents = $auto->getMedia('documents')->map(function ($m) {
            return [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'name' => $m->name,
                'file_name' => $m->file_name,
            ];
        })->values();

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

        $current = $auto->currentLocation;
        $currentLocation = $current ? [
            'id' => $current->id,
            'type' => $current->location_type,
            'type_label' => $mapTypeLabel($current->location_type),
            'name' => $current->location->name ?? ($current->location->title ?? null),
            'status' => $current->status,
            'status_label' => Statuses::from((int) $current->status)->lable(),
            'started_at' => optional($current->started_at)->toDateTimeString(),
            'ended_at' => optional($current->ended_at)->toDateTimeString(),
            'accepted_by' => $current->acceptedBy ? [
                'id' => $current->acceptedBy->id,
                'name' => $current->acceptedBy->name,
            ] : null,
            'acceptance_note' => $current->acceptance_note,
        ] : null;

        $periods = $auto->locationPeriods->map(function ($p) use ($mapTypeLabel) {
            return [
                'id' => $p->id,
                'type' => $p->location_type,
                'type_label' => $mapTypeLabel($p->location_type),
                'name' => $p->location->name ?? ($p->location->title ?? null),
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

        $payload = [
            'id' => $auto->id,
            'title' => $auto->title,
            'vin' => $auto->vin,
            'year' => $auto->year ? date('Y', strtotime((string) $auto->year)) : null,
            'price' => $auto->price,
            'departure_date' => $auto->departure_date ? date('Y-m-d', strtotime((string) $auto->departure_date)) : null,
            'status' => $auto->status,
            'status_label' => Statuses::from((int) $auto->status)->lable(),
            'brand' => $auto->autoModel->brand->name ?? null,
            'model' => $auto->autoModel->name ?? null,
            'color' => $auto->color->name ?? null,
            'company' => $auto->company ? ['id' => $auto->company->id, 'name' => $auto->company->name] : null,
            'sender' => $auto->sender ? ['id' => $auto->sender->id, 'name' => $auto->sender->name] : null,
            'provider' => $auto->provider ? [
                'id' => $auto->provider->id,
                'name' => $auto->provider->name,
                'user' => $auto->provider->user ? ['id' => $auto->provider->user->id, 'name' => $auto->provider->user->name] : null,
            ] : null,
            'media' => [
                'photos' => $photos,
                'videos' => $videos,
                'documents' => $documents,
            ],
            'current_location' => $currentLocation,
            'periods' => $periods,
        ];

        return Inertia::render('Autos/Show', [
            'auto' => $payload,
        ]);
    }

    public function create()
    {
        $brands = AutoBrand::query()->select('id', 'name')->orderBy('name')->get();
        $colors = Color::query()->select('id', 'name')->orderBy('name')->get();
        return Inertia::render('Autos/Create', [
            'brands' => $brands,
            'colors' => $colors,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
                'departure_date' => ['nullable', 'date'],
                'auto_brand_id' => ['required', 'integer', 'exists:auto_brands,id'],
                'auto_model_id' => ['required', 'integer', 'exists:auto_models,id'],
                'color_id' => ['nullable', 'integer', 'exists:colors,id'],
                'vin' => ['required', 'string', 'max:255', 'unique:autos,vin'],
                'year' => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:'.date('Y')],
                'price' => ['nullable', 'integer', 'min:0'],
                'photos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
                'videos.*' => ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:51200'],
                'documents.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:20480'],
            ]);

            $model = AutoModel::with('brand:id,name')->findOrFail($data['auto_model_id']);
            if ((int)$model->brand_id !== (int)$data['auto_brand_id']) {
                return back()->withErrors(['auto_model_id' => 'Модель не принадлежит выбранному бренду.'])->withInput();
            }

            $user = $request->user();
            $sender = Sender::firstOrCreate(
                ['user_id' => $user->id, 'company_id' => $user->company_id],
                ['name' => $user->name]
            );
            $provider = Provider::firstOrCreate(
                ['user_id' => $user->id, 'company_id' => $user->company_id],
                ['name' => $user->name]
            );

            $yearDate = !empty($data['year']) ? sprintf('%04d-01-01', (int)$data['year']) : null;

            $title = trim(($model->brand->name ?? '').' '.($model->name ?? '').' '.$data['vin']);

            $auto = Auto::create([
                'title' => $title,
                'departure_date' => $data['departure_date'] ?? null,
                'auto_model_id' => $data['auto_model_id'],
                'color_id' => $data['color_id'] ?? null,
                'company_id' => $user->company_id,
                'sender_id' => $sender->id,
                'provider_id' => $provider->id,
                'vin' => $data['vin'],
                'year' => $yearDate,
                'price' => $data['price'] ?? null,
                'status' => \App\Enums\Statuses::Delivery->value,
            ]);

            foreach ((array)($request->file('photos') ?? []) as $file) {
                $auto->addMedia($file)->toMediaCollection('photos');
            }
            foreach ((array)($request->file('videos') ?? []) as $file) {
                $auto->addMedia($file)->toMediaCollection('videos');
            }
            foreach ((array)($request->file('documents') ?? []) as $file) {
                $auto->addMedia($file)->toMediaCollection('documents');
            }

            return redirect()->route('autos.index')->with('success', 'Автомобиль добавлен');
    }
}
