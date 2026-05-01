<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_order_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 8, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2)->storedAs('quantity * unit_price');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_parts');
    }
};
