<?php
// database/migrations/2025_09_02_000009_create_eligibility_degrees_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('eligibility_degrees', function (Blueprint $table) {
            $table->id();
            $table->string('degree');
            $table->string('institute')->nullable();
            $table->string('country', 100)->nullable();
            $table->decimal('cgpa', 4, 2)->nullable();
            $table->date('date_graduation')->nullable();
            $table->string('duration')->nullable();
            $table->decimal('total_credit', 5, 2)->nullable();
            $table->string('mode')->nullable();
            $table->string('period')->nullable();
            $table->string('uni_status')->nullable();
            $table->string('url', 2048)->nullable();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('eligibility_degrees');
    }
};
