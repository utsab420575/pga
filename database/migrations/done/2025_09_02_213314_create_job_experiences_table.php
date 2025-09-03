<?php
// database/migrations/2025_09_02_000005_create_job_experiences_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('job_experiences', function (Blueprint $table) {
            $table->id();
            $table->date('from')->nullable(); // kept exactly as you provided
            $table->date('to')->nullable();   // kept exactly as you provided
            $table->string('designation')->nullable();
            $table->string('organization')->nullable();
            $table->text('details')->nullable();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('job_experiences');
    }
};
