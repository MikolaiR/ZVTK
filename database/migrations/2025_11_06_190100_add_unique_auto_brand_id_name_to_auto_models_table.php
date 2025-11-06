<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('auto_models', function (Blueprint $table) {
            $table->unique(['auto_brand_id', 'name'], 'auto_models_brand_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_models', function (Blueprint $table) {
            $table->dropUnique('auto_models_brand_name_unique');
        });
    }
};
