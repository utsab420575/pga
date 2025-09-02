<?php
// database/migrations/2025_09_02_000001_create_basic_infos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('basic_infos', function (Blueprint $table) {
            $table->id();
            $table->string('bn_name')->nullable();
            $table->string('f_name')->nullable();
            $table->string('m_name')->nullable();
            $table->string('g_incode')->nullable();
            $table->string('passport_no')->nullable();
            $table->text('per_address')->nullable();
            $table->text('pre_address')->nullable();
            $table->date('dob')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('nid', 100)->nullable();
            $table->string('religion', 100)->nullable();
            $table->string('gender', 50)->nullable();
            $table->string('marital_status', 50)->nullable();
            $table->string('field_of_interest')->nullable();
            $table->string('photo')->nullable();
            $table->string('sign')->nullable();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('basic_infos');
    }
};
