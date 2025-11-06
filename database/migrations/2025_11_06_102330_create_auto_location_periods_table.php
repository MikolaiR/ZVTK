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
        Schema::create('auto_location_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auto_id')->constrained()->cascadeOnDelete();
            $table->morphs('location');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('acceptance_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['auto_id', 'ended_at']);
            $table->index(['location_type', 'location_id', 'ended_at']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_location_periods');
    }
};
