<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->enum('type', ['inspection', 'repair', 'maintenance', 'diagnostic', 'other'])->default('repair');
            $table->enum('status', ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
