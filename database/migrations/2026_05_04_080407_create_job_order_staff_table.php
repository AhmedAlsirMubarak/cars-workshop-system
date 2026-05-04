<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_order_staff', function (Blueprint $table) {
            $table->foreignId('job_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->primary(['job_order_id', 'staff_id']);
        });

        // Backfill existing single staff_id assignments into the pivot
        DB::table('job_orders')
            ->whereNotNull('staff_id')
            ->select('id', 'staff_id')
            ->get()
            ->each(function ($job) {
                DB::table('job_order_staff')->insertOrIgnore([
                    'job_order_id' => $job->id,
                    'staff_id'     => $job->staff_id,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_staff');
    }
};
