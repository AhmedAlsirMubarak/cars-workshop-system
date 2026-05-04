<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(5)->change();
        });

        DB::table('job_orders')->where('tax_rate', 15)->update(['tax_rate' => 5]);
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(15)->change();
        });
    }
};
