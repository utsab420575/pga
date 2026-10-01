<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            // public | private | previously_eligible  (null for eligibility applications)
            $table->string('university_type', 30)
                  ->nullable()
                  ->default(null)
                  ->after('applicationtype_id')
                  ->comment('public | private | previously_eligible');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('university_type');
        });
    }
};
