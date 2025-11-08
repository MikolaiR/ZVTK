<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('bases/colors_base.json');
        if (!is_file($path)) {
            throw new \RuntimeException("Source file not found: {$path}");
        }

        $json = file_get_contents($path);
        if ($json === false || $json === '') {
            throw new \RuntimeException("Failed to read source file: {$path}");
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Failed to decode JSON from colors_base.json');
        }

        $items = $decoded['data'] ?? $decoded;
        if (!is_array($items)) {
            throw new \RuntimeException('Unexpected data format in colors_base.json');
        }

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                if (!isset($item['name'], $item['name_ru'], $item['hex_code'])) {
                    continue;
                }

                $name = trim((string) $item['name']);
                $nameRu = trim((string) $item['name_ru']);
                $hex = strtoupper(trim((string) $item['hex_code']));

                $color = Color::withTrashed()->where('name', $name)->first();
                if (!$color) {
                    $color = Color::withTrashed()->where('hex_code', $hex)->first();
                }

                if ($color) {
                    $color->name = $name;
                    $color->name_ru = $nameRu;
                    $color->hex_code = $hex;
                    if ($color->trashed()) {
                        $color->restore();
                    }
                    $color->save();
                } else {
                    Color::create([
                        'name' => $name,
                        'name_ru' => $nameRu,
                        'hex_code' => $hex,
                    ]);
                }
            }
        });
    }
}
