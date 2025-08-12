<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cost_estimate_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_estimate_id')->constrained()->onDelete('cascade');
            $table->string('item_code')->nullable()->comment('Item or service code');
            $table->string('description')->comment('Item or service description');
            $table->string('unit')->comment('Unit of measurement');
            $table->decimal('quantity', 10, 2)->comment('Quantity required');
            $table->decimal('unit_price', 12, 2)->comment('Price per unit');
            $table->decimal('total_price', 15, 2)->comment('Total price for this item');
            $table->text('notes')->nullable()->comment('Additional notes for this item');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->timestamps();
            
            $table->index('cost_estimate_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_estimate_items');
    }
};