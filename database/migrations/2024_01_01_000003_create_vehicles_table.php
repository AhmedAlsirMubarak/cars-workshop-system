<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('make');
            $table->string('model');
            $table->year('year');
            $table->string('color')->nullable();
            $table->string('plate_number')->unique();
            $table->string('vin')->unique()->nullable();
            $table->string('engine_type')->nullable();
            $table->integer('mileage')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
