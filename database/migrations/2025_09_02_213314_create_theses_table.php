<?php
// database/migrations/2025_09_02_000003_create_theses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('theses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('institute')->nullable();
            $table->string('period')->nullable();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('theses');
    }
};
