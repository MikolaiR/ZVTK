<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
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
