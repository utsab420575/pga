<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Replaced by the attachment_requirements table
    public function up(): void
    {
        Schema::table('attachment_types', function (Blueprint $table) {
            $table->dropColumn('required');
        });
    }

    public function down(): void
    {
        Schema::table('attachment_types', function (Blueprint $table) {
            $table->tinyInteger('required')->default(0)->after('status');
        });
    }
};
