<?php

namespace App\Http\Resources;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Support\MediaLibrary\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Auto
 */
class AutoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $makeUrl = fn($m, $conv = '') => MediaUrl::url($m, $conv);

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

        $photos = $this->getMedia('photos')->map(function ($m) use ($makeUrl) {
            return [
                'id' => $m->id,
                // main display URL (larger)
                'url' => $makeUrl($m, 'preview'),
                'thumb_url' => $makeUrl($m, 'thumb'),
                'full_url' => $makeUrl($m, 'large'),
                'name' => $m->name,
                'file_name' => $m->file_name,
            ];
        })->values();
        $videos = $this->getMedia('videos')->map(function ($m) use ($makeUrl) {
            return [
                'id' => $m->id,
                'url' => $makeUrl($m),
                'name' => $m->name,
                'file_name' => $m->file_name,
            ];
        })->values();
        $documents = $this->getMedia('documents')->map(function ($m) use ($makeUrl) {
            return [
                'id' => $m->id,
                'url' => $makeUrl($m),
                'name' => $m->name,
                'file_name' => $m->file_name,
            ];
        })->values();

        $current = $this->currentLocation;
        $currentLocation = $current ? [
            'id' => $current->id,
            'type' => $current->location_type,
            'type_label' => $mapTypeLabel($current->location_type),
            'name' => $current->location->name ?? ($current->location->title ?? null),
            'location_id' => $current->location?->id,
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

        $periods = $this->locationPeriods->map(function ($p) use ($mapTypeLabel) {
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

        return [
            'id' => $this->id,
            'title' => $this->title,
            'vin' => $this->vin,
            'year' => $this->year ? date('Y', strtotime((string) $this->year)) : null,
            'price' => $this->price,
            'departure_date' => $this->departure_date ? date('Y-m-d', strtotime((string) $this->departure_date)) : null,
            'status' => $this->status->value,
            'status_label' => Statuses::from($this->status->value)->lable(),
            'brand' => $this->model->brand->name ?? null,
            'model' => $this->model->name ?? null,
            'color' => $this->color->name ?? null,
            'company' => $this->company ? ['id' => $this->company->id, 'name' => $this->company->name] : null,
            'sender' => $this->sender ? ['id' => $this->sender->id, 'name' => $this->sender->name] : null,
            'provider' => $this->provider ? [
                'id' => $this->provider->id,
                'name' => $this->provider->name,
                'user' => $this->provider->user ? ['id' => $this->provider->user->id, 'name' => $this->provider->user->name] : null,
            ] : null,
            'media' => [
                'photos' => $photos,
                'videos' => $videos,
                'documents' => $documents,
            ],
            'current_location' => $currentLocation,
            'periods' => $periods,
        ];
    }
}
