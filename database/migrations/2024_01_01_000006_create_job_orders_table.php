<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'waiting_parts', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('complaint')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('recommendations')->nullable();
            $table->decimal('labour_cost', 10, 2)->default(0);
            $table->decimal('parts_cost', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(15);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('promised_at')->nullable();
            $table->integer('mileage_in')->nullable();
            $table->integer('mileage_out')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};
