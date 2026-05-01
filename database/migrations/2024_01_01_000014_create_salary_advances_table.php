<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->decimal('amount', 10, 3);
            $table->date('requested_on');
            $table->date('approved_on')->nullable();
            $table->unsignedTinyInteger('deduct_month');  // 1-12
            $table->unsignedSmallInteger('deduct_year');
            $table->enum('status', ['pending', 'approved', 'rejected', 'deducted'])->default('pending');
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
    }
};
