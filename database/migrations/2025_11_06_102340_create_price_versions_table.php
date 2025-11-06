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
        Schema::create('price_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->dateTime('valid_from');
            $table->dateTime('valid_to')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['price_id', 'valid_from']);
            $table->index(['price_id', 'valid_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_versions');
    }
};
