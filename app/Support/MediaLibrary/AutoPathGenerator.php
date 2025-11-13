<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Illuminate\Support\Facades\Storage;

class AutoPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        return $this->base($media);
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        $relative = $this->base($media).'conversions/';
        try {
            $absolute = Storage::disk($media->disk)->path($relative);
            if (!is_dir($absolute)) {
                @mkdir($absolute, 0777, true);
                @chmod($absolute, 0777);
            }
        } catch (\Throwable $e) {
        }
        return $relative;
    }

    /**
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        $relative = $this->base($media).'responsive-images/';
        try {
            $absolute = Storage::disk($media->disk)->path($relative);
            if (!is_dir($absolute)) {
                @mkdir($absolute, 0777, true);
                @chmod($absolute, 0777);
            }
        } catch (\Throwable $e) {
        }
        return $relative;
    }

    private function base(Media $media): string
    {
        // Store inside autos/{auto_id}/
        $autoId = (int) $media->model_id;
        $relative = "autos/{$autoId}/";
        try {
            $absolute = Storage::disk($media->disk)->path($relative);
            if (!is_dir($absolute)) {
                @mkdir($absolute, 0777, true);
                @chmod($absolute, 0777);
            }
        } catch (\Throwable $e) {
        }
        return $relative;
    }
}
