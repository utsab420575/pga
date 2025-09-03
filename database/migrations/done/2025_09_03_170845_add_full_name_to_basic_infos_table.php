<?php
// database/migrations/2025_09_03_000001_add_full_name_to_basic_info_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('basic_infos') && !Schema::hasColumn('basic_infos', 'full_name')) {
            Schema::table('basic_infos', function (Blueprint $table) {
                $table->string('full_name')->nullable()->after('id');
            });
        }

    }

    public function down(): void
    {
        if (Schema::hasTable('basic_infos') && Schema::hasColumn('basic_infos', 'full_name')) {
            Schema::table('basic_infos', function (Blueprint $table) {
                $table->dropColumn('full_name');
            });
        }

    }
};
