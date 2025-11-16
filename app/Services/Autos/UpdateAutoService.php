<?php

namespace App\Services\Autos;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoModel;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class UpdateAutoService
{
    public function __construct(private readonly DB $db)
    {
    }

    /**
     * @param array{title?:string,departure_date?:string|null,auto_brand_id:int,auto_model_id:int,color_id?:int|null,company_id?:int|null,sender_id?:int|null,provider_id?:int|null,vin:string,year?:int|null,price?:int|null,status?:int|null,remove_media?:array<int>,photos?:UploadedFile[],videos?:UploadedFile[],documents?:UploadedFile[]} $data
     */
    public function handle(Auto $auto, array $data): Auto
    {
        $model = AutoModel::with('brand:id,name')->findOrFail($data['auto_model_id']);
        if ((int) $model->auto_brand_id !== (int) $data['auto_brand_id']) {
            throw ValidationException::withMessages([
                'auto_model_id' => __('The selected model does not belong to the chosen brand.'),
            ]);
        }

        $yearDate = !empty($data['year']) ? sprintf('%04d-01-01', (int) $data['year']) : null;
        $computedTitle = trim(($model->brand->name ?? '') . ' ' . ($model->name ?? '') . ' ' . $data['vin']);
        $title = isset($data['title']) && $data['title'] !== '' ? $data['title'] : $computedTitle;

        return $this->db->transaction(function () use ($auto, $data, $title, $yearDate) {
            $payload = [
                'title' => $title,
                'departure_date' => $data['departure_date'] ?? null,
                'auto_model_id' => $data['auto_model_id'],
                'color_id' => $data['color_id'] ?? null,
                'company_id' => $data['company_id'] ?? null,
                'sender_id' => $data['sender_id'] ?? null,
                'provider_id' => $data['provider_id'] ?? null,
                'vin' => $data['vin'],
                'year' => $yearDate,
                'price' => $data['price'] ?? null,
            ];
            if (isset($data['status'])) {
                $payload['status'] = (int) $data['status'];
            }

            $auto->update($payload);

            foreach ((array)($data['remove_media'] ?? []) as $mediaId) {
                $m = $auto->media()->whereKey($mediaId)->first();
                if ($m) $m->delete();
            }

            foreach ((array)($data['photos'] ?? []) as $file) {
                if ($file instanceof UploadedFile) {
                    $auto->addMedia($file)->toMediaCollection('photos');
                }
            }
            foreach ((array)($data['videos'] ?? []) as $file) {
                if ($file instanceof UploadedFile) {
                    $auto->addMedia($file)->toMediaCollection('videos');
                }
            }
            foreach ((array)($data['documents'] ?? []) as $file) {
                if ($file instanceof UploadedFile) {
                    $auto->addMedia($file)->toMediaCollection('documents');
                }
            }

            return $auto;
        });
    }
}
