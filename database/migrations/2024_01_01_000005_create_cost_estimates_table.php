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
        Schema::create('cost_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('ce_number')->unique()->comment('Unique cost estimate number');
            $table->string('title')->comment('Cost estimate title');
            $table->text('description')->nullable()->comment('Detailed description');
            $table->enum('type', ['cost_estimate', 'bill_of_quantities'])->default('cost_estimate')->comment('Type of estimate');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Total estimated amount');
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected'])->default('draft')->comment('Approval status');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable()->comment('When the CE/BOQ was approved');
            $table->text('approval_notes')->nullable()->comment('Notes from approval process');
            $table->text('rejection_notes')->nullable()->comment('Notes if rejected');
            $table->timestamps();
            
            $table->index('ce_number');
            $table->index('purchase_order_id');
            $table->index('status');
            $table->index('type');
            $table->index('created_by');
            $table->index('approved_by');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_estimates');
    }
};