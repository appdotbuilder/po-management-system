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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique()->comment('Unique purchase order number');
            $table->string('title')->comment('Purchase order title');
            $table->text('description')->nullable()->comment('Detailed description of the purchase order');
            $table->decimal('estimated_value', 15, 2)->nullable()->comment('Estimated total value');
            $table->enum('status', ['draft', 'pending_validation', 'validated', 'pending_ce_boq', 'ce_boq_created', 'ce_boq_approved', 'in_progress', 'completed', 'cancelled'])
                  ->default('draft')
                  ->comment('Current status of the purchase order');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->comment('Priority level');
            $table->date('required_by')->nullable()->comment('Required completion date');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable()->comment('When the PO was validated');
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->timestamp('completed_at')->nullable()->comment('When the PO was completed');
            $table->text('validation_notes')->nullable()->comment('Notes from validation process');
            $table->text('completion_notes')->nullable()->comment('Notes from completion');
            $table->timestamps();
            
            $table->index('po_number');
            $table->index('status');
            $table->index('priority');
            $table->index('created_by');
            $table->index('validated_by');
            $table->index(['status', 'created_at']);
            $table->index(['priority', 'required_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};