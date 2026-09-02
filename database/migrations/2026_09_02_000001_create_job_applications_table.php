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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('company_name')->index();
            $table->string('position')->index();
            $table->enum('status', [
                'applied',
                'screening',
                'interview',
                'offer',
                'rejected',
                'accepted',
            ])->default('applied')->index();
            $table->date('applied_date')->index();
            $table->string('source')->nullable();
            $table->string('job_url', 500)->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('salary_range_min', 15, 2)->nullable();
            $table->decimal('salary_range_max', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
