<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');    // 1–12
            $table->unsignedSmallInteger('year');

            // ── Earnings ──────────────────────────────────────
            $table->decimal('basic_salary', 10, 3)->default(0);
            $table->decimal('bonus', 10, 3)->default(0);
            $table->string('bonus_note')->nullable();
            $table->decimal('gross_salary', 10, 3)->default(0);  // basic + bonus

            // ── Working days snapshot ─────────────────────────
            $table->unsignedTinyInteger('working_days')->default(26); // configured working days that month
            $table->unsignedTinyInteger('days_present')->default(0);
            $table->unsignedTinyInteger('days_absent')->default(0);
            $table->unsignedTinyInteger('days_half')->default(0);     // half-days count as 0.5
            $table->decimal('hours_worked', 7, 2)->default(0);

            // ── Deductions ────────────────────────────────────
            $table->decimal('absence_deduction', 10, 3)->default(0); // auto: (basic/working_days) × absent_days
            $table->decimal('advance_deduction', 10, 3)->default(0); // advances approved for this month
            $table->decimal('other_deduction', 10, 3)->default(0);
            $table->string('other_deduction_note')->nullable();
            $table->decimal('total_deductions', 10, 3)->default(0);

            // ── Net ───────────────────────────────────────────
            $table->decimal('net_salary', 10, 3)->default(0);        // gross - total_deductions

            // ── Payment ───────────────────────────────────────
            $table->enum('payment_method', ['bank_transfer', 'cash', 'split'])->default('bank_transfer');
            $table->decimal('bank_amount', 10, 3)->default(0);       // used when split
            $table->decimal('cash_amount', 10, 3)->default(0);       // used when split
            $table->string('payment_reference')->nullable();

            // ── Workflow ──────────────────────────────────────
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->date('paid_on')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // One payslip per employee per month
            $table->unique(['staff_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
