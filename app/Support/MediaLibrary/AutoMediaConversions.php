<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\HasMedia;
use Spatie\Image\Enums\Fit;

class AutoMediaConversions
{
    public function register(HasMedia $model): void
    {
        $model->addMediaConversion('thumb')
            ->performOnCollections('photos')
            ->format('webp')
            ->fit(Fit::Contain, 320, 240)
            ->nonQueued();

        $model->addMediaConversion('preview')
            ->performOnCollections('photos')
            ->format('webp')
            ->fit(Fit::Max, 800, 800)
            ->nonQueued();

        $model->addMediaConversion('large')
            ->performOnCollections('photos')
            ->format('webp')
            ->fit(Fit::Max, 1600, 1600);
    }
}
