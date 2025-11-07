<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutoBrand;
use App\Models\AutoModel;
use Illuminate\Http\Request;

class BrandAndModelController extends Controller
{
    public function getModels(AutoBrand $brand)
    {
        return AutoModel::query()->where('brand_id', $brand->id)->select('id', 'name')->orderBy('name')->get();
    }
}
