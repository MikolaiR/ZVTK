<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaUrl
{
    public static function url(Media $media, string $conversion = ''): string
    {
        try {
            if ($media->disk === 'local') {
                return $media->getTemporaryUrl(
                    now()->addMinutes((int) config('media-library.temporary_url_default_lifetime', 5)),
                    $conversion
                );
            }
        } catch (\Throwable $e) {
            // fallback below
        }
        return $media->getUrl($conversion);
    }
}
