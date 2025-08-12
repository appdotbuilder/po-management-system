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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['superadmin', 'admin', 'unit_kerja', 'bsp', 'kkf', 'dau'])
                  ->default('unit_kerja')
                  ->after('email')
                  ->comment('User role for permission management');
            $table->boolean('is_active')->default(true)->after('role')->comment('User active status');
            $table->timestamp('last_login_at')->nullable()->after('is_active')->comment('Last login timestamp');
            
            $table->index('role');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['role', 'is_active', 'last_login_at']);
        });
    }
};