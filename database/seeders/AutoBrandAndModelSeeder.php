<?php

namespace Database\Seeders;

use App\Models\AutoBrand;
use App\Models\AutoModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AutoBrandAndModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('bases/cars_base.json');
        if (!is_file($path)) {
            throw new \RuntimeException("Source file not found: {$path}");
        }

        $json = file_get_contents($path);
        if ($json === false || $json === '') {
            throw new \RuntimeException("Failed to read source file: {$path}");
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Failed to decode JSON from cars_base.txt');
        }

        $brands = $decoded['data'] ?? $decoded;
        if (!is_array($brands)) {
            throw new \RuntimeException('Unexpected data format in cars_base.txt');
        }

        DB::transaction(function () use ($brands) {
            foreach ($brands as $brand) {
                if (!isset($brand['id'], $brand['name'])) {
                    continue;
                }

                $brandId = (string) $brand['id'];
                $brandName = (string) $brand['name'];

                $brandModel = AutoBrand::withTrashed()->find($brandId);
                if ($brandModel) {
                    $brandModel->name = $brandName;
                    if ($brandModel->trashed()) {
                        $brandModel->restore();
                    }
                    $brandModel->save();
                } else {
                    $brandModel = new AutoBrand([
                        'id' => $brandId,
                        'name' => $brandName,
                    ]);
                    $brandModel->save();
                }

                if (!empty($brand['models']) && is_array($brand['models'])) {
                    foreach ($brand['models'] as $model) {
                        if (!isset($model['name'])) {
                            continue;
                        }

                        $modelName = (string) $model['name'];

                        $existingModel = AutoModel::withTrashed()
                            ->where('auto_brand_id', $brandId)
                            ->where('name', $modelName)
                            ->first();

                        if ($existingModel) {
                            if ($existingModel->trashed()) {
                                $existingModel->restore();
                            }
                            $existingModel->auto_brand_id = $brandId;
                            $existingModel->name = $modelName;
                            $existingModel->save();
                        } else {
                            AutoModel::create([
                                'auto_brand_id' => $brandId,
                                'name' => $modelName,
                            ]);
                        }
                    }
                }
            }
        });
    }
}
