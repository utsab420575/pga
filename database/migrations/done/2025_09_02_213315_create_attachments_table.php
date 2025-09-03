<?php
// database/migrations/2025_09_02_000008_create_attachments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('file');
            $table->foreignId('attachment_type_id')->constrained('attachment_types')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('attachments');
    }
};
