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
        Schema::table('settings', function (Blueprint $table) {
            $table->date('eligibility_approval_start_date')->nullable()->after('last_eligibility_payment_date');
            $table->date('eligibility_approval_last_date')->nullable()->after('eligibility_approval_start_date');
            $table->date('admission_approval_start_date')->nullable()->after('eligibility_approval_last_date');
            $table->date('admission_approval_last_date')->nullable()->after('admission_approval_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'eligibility_approval_start_date',
                'eligibility_approval_last_date',
                'admission_approval_start_date',
                'admission_approval_last_date',
            ]);
        });
    }
};
