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
            $table->string('role')->default('user')->after('email'); // 'user' | 'admin'
            $table->string('headline')->nullable()->after('name'); // e.g. "Senior Frontend Engineer"
            $table->string('phone')->nullable()->after('headline');
            $table->decimal('target_salary_min', 15, 2)->nullable()->after('phone');
            $table->decimal('target_salary_max', 15, 2)->nullable()->after('target_salary_min');
            $table->string('preferred_location')->nullable()->after('target_salary_max'); // e.g. "Remote / Jakarta"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'headline',
                'phone',
                'target_salary_min',
                'target_salary_max',
                'preferred_location',
            ]);
        });
    }
};
