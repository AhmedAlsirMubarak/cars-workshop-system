<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('reorder_level')->default(5);
            $table->string('location')->nullable();
            $table->string('supplier')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
