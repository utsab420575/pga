<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Slim version of spatie/laravel-activitylog's table.
     * Dropped: description (duplicates event), batch_uuid (unused), updated_at (logs are never edited).
     * Added:   applicant_id (filter all history of one application), ip_address.
     */
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name', 50)->nullable()->index();      // table name, e.g. basic_infos
            $table->string('event', 20)->nullable();                  // created / updated / deleted / cloned
            $table->nullableMorphs('subject');                        // the changed row
            $table->nullableMorphs('causer');                         // the logged-in user who did it
            $table->unsignedBigInteger('applicant_id')->nullable()->index();
            $table->json('properties')->nullable();                   // only changed fields: {attributes, old}
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
