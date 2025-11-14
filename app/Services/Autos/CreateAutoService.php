<?php

namespace App\Services\Autos;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoModel;
use App\Models\Provider;
use App\Models\Sender;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class CreateAutoService
{
    public function __construct(private readonly DB $db)
    {
    }

    /**
     * Create an Auto with derived attributes and media attachments.
     *
     * @param array $data Validated payload from CreateRequest
     * @param User $actor Authenticated user creating the auto
     * @param array{photos?:UploadedFile[],videos?:UploadedFile[],documents?:UploadedFile[]} $media
     */
    public function handle(array $data, User $actor, array $media = []): Auto
    {
        $model = AutoModel::with('brand:id,name')->findOrFail($data['auto_model_id']);
        if ((int) $model->auto_brand_id !== (int) $data['auto_brand_id']) {
            throw ValidationException::withMessages([
                'auto_model_id' => __('The selected model does not belong to the chosen brand.'),
            ]);
        }

        $sender = Sender::firstOrCreate(
            ['user_id' => $actor->id, 'company_id' => $actor->company_id],
            ['name' => $actor->name]
        );
        $provider = Provider::firstOrCreate(
            ['user_id' => $actor->id, 'company_id' => $actor->company_id],
            ['name' => $actor->name]
        );

        $yearDate = !empty($data['year']) ? sprintf('%04d-01-01', (int) $data['year']) : null;
        $title = trim(($model->brand->name ?? '') . ' ' . ($model->name ?? '') . ' ' . $data['vin']);

        return $this->db->transaction(function () use ($data, $actor, $sender, $provider, $yearDate, $title, $media) {
            $auto = Auto::create([
                'title' => $title,
                'departure_date' => $data['departure_date'] ?? null,
                'auto_model_id' => $data['auto_model_id'],
                'color_id' => $data['color_id'] ?? null,
                'company_id' => $actor->company_id,
                'sender_id' => $sender->id,
                'provider_id' => $provider->id,
                'vin' => $data['vin'],
                'year' => $yearDate,
                'price' => $data['price'] ?? null,
                'status' => Statuses::Delivery->value,
            ]);

            foreach ((array)($media['photos'] ?? []) as $file) {
                if ($file instanceof UploadedFile) {
                    $auto->addMedia($file)->toMediaCollection('photos');
                }
            }
            foreach ((array)($media['videos'] ?? []) as $file) {
                if ($file instanceof UploadedFile) {
                    $auto->addMedia($file)->toMediaCollection('videos');
                }
            }
            foreach ((array)($media['documents'] ?? []) as $file) {
                if ($file instanceof UploadedFile) {
                    $auto->addMedia($file)->toMediaCollection('documents');
                }
            }

            return $auto;
        });
    }
}
