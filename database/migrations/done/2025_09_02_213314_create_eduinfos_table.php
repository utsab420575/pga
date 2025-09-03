<?php
// database/migrations/2025_09_02_000002_create_education_infos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('education_infos', function (Blueprint $table) {
            $table->id();
            $table->string('degree');
            $table->string('institute');
            $table->unsignedSmallInteger('year_of_passing')->nullable();
            $table->string('field')->nullable();
            $table->decimal('cgpa', 4, 2)->nullable();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('education_infos');
    }
};
