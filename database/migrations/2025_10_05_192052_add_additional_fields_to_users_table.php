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
            $table->boolean('is_active')->default(true)->after('email_verified_at');
            $table->string('avatar')->nullable()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('avatar');
            $table->softDeletes()->after('updated_at');

            // Add indexes for frequently queried columns
            $table->index('is_active');
            $table->index('last_login_at');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['last_login_at']);
            $table->dropIndex(['deleted_at']);
            $table->dropSoftDeletes();
            $table->dropColumn(['is_active', 'avatar', 'last_login_at']);
        });
    }
};
