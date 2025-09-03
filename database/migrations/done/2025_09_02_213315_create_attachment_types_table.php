<?php
// database/migrations/2025_09_02_000007_create_attachment_types_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attachment_types', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->boolean('status')->default(true);
            $table->boolean('required')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('attachment_types');
    }
};
