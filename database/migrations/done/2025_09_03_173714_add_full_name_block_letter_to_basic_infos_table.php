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
        // Preferred plural table
        if (Schema::hasTable('basic_infos') && !Schema::hasColumn('basic_infos', 'full_name_block_letter')) {
            Schema::table('basic_infos', function (Blueprint $table) {
                $table->string('full_name_block_letter')->nullable()->after('full_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('basic_infos') && Schema::hasColumn('basic_infos', 'full_name_block_letter')) {
            Schema::table('basic_infos', function (Blueprint $table) {
                $table->dropColumn('full_name_block_letter');
            });
        }
    }
};
