<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->decimal('basic_salary', 10, 3)->default(0)->after('hourly_rate')
                  ->comment('Fixed monthly salary in OMR');
            $table->string('bank_name')->nullable()->after('basic_salary');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('iban')->nullable()->after('bank_account_number');
            $table->enum('payment_method', ['bank_transfer', 'cash', 'both'])->default('bank_transfer')->after('iban');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['basic_salary', 'bank_name', 'bank_account_number', 'iban', 'payment_method']);
        });
    }
};
