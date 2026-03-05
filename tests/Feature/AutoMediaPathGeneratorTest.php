<?php

namespace Tests\Feature;

use App\Enums\Statuses;
use App\Models\Auto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AutoMediaPathGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_media_item_gets_unique_directory_path_for_same_filename(): void
    {
        Storage::fake('local');

        $auto = Auto::withoutEvents(fn () => Auto::query()->create([
            'title' => 'Path Test Car',
            'vin' => 'VIN-PATH-001',
            'status' => Statuses::Delivery,
        ]));

        $first = $auto->addMedia(UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg'))
            ->toMediaCollection('photos');

        $second = $auto->addMedia(UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg'))
            ->toMediaCollection('photos');

        $firstPath = str_replace('\\', '/', $first->getPathRelativeToRoot());
        $secondPath = str_replace('\\', '/', $second->getPathRelativeToRoot());

        $this->assertNotSame($firstPath, $secondPath);
        $this->assertStringContainsString("/{$first->id}/", $firstPath);
        $this->assertStringContainsString("/{$second->id}/", $secondPath);
    }
}
