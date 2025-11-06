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
        Schema::create('parking_price_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_id')->constrained()->cascadeOnDelete();
            $table->dateTime('valid_from');
            $table->dateTime('valid_to')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parking_id', 'valid_from']);
            $table->index(['parking_id', 'valid_to']);
            $table->index(['parking_id', 'price_id', 'valid_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_price_assignments');
    }
};
