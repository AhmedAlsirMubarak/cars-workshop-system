<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('hours_worked', 5, 2)->default(0); // auto-computed on save
            $table->enum('status', ['present', 'absent', 'half_day', 'leave', 'holiday'])
                  ->default('present');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // One record per employee per day
            $table->unique(['staff_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
