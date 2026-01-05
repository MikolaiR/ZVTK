<?php

namespace App\Services\Autos\Transitions;

use App\Models\Auto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaveFilesTransition implements AutoTransition
{
    public function handle(Auto $auto, array $data, User $actor): void
    {
        DB::transaction(function () use ($auto, $data) {
            foreach ((array) ($data['photos'] ?? []) as $file) {
                $auto->addMedia($file)->toMediaCollection('photos');
            }
            foreach ((array) ($data['videos'] ?? []) as $file) {
                $auto->addMedia($file)->toMediaCollection('videos');
            }
            foreach ((array) ($data['documents'] ?? []) as $file) {
                $auto->addMedia($file)->toMediaCollection('documents');
            }
        });
    }
}
