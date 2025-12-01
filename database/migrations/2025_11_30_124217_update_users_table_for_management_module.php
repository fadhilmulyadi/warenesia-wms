<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspended', 'pending'])
                ->default('active')
                ->after('is_approved');

            $table->string('department')->nullable()->after('status');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->timestamp('approved_at')->nullable()->after('last_login_at');
            $table->foreignId('approved_by')
                ->nullable()
                ->after('approved_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->softDeletes();
        });

        DB::table('users')
            ->where('role', 'supplier')
            ->where(function ($query) {
                $query->whereNull('is_approved')->orWhere('is_approved', false);
            })
            ->update(['status' => 'pending']);

        DB::table('users')
            ->where('role', 'supplier')
            ->where('is_approved', true)
            ->update([
                'status' => 'active',
                'approved_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'status',
                'department',
                'last_login_at',
                'approved_at',
                'approved_by',
            ]);
        });
    }
};