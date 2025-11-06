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
            // Drop existing FK (likely auto_models_auto_brand_id_foreign)
            $table->dropForeign(['auto_brand_id']);
        });

        Schema::table('auto_models', function (Blueprint $table) {
            // Recreate with RESTRICT on delete
            $table->foreign('auto_brand_id')
                ->references('id')->on('auto_brands')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_models', function (Blueprint $table) {
            $table->dropForeign(['auto_brand_id']);
        });

        Schema::table('auto_models', function (Blueprint $table) {
            $table->foreign('auto_brand_id')
                ->references('id')->on('auto_brands')
                ->cascadeOnDelete();
        });
    }
};
