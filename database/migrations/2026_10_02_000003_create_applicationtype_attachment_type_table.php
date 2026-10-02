<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which attachment types are offered (shown in the upload dropdown) per application type.
     * A row = offered. Required types (attachment_requirements) must also be offered.
     */
    public function up(): void
    {
        Schema::create('applicationtype_attachment_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicationtype_id')->constrained('applicationtypes')->cascadeOnDelete();
            $table->foreignId('attachment_type_id')->constrained('attachment_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['applicationtype_id', 'attachment_type_id'], 'apptype_attachment_type_unique');
        });

        // Seed from the lists previously hardcoded in the forms' reject([...]) filters
        $hidden = [
            1 => [13, 16], // Admission: application_postgraduate_master_form
            2 => [11, 12], // Eligibility: eligibility_master_form
        ];

        $typeIds = DB::table('attachment_types')->orderBy('id')->pluck('id');
        $appIds  = DB::table('applicationtypes')->pluck('id');

        $rows = [];
        foreach ($appIds as $appId) {
            foreach ($typeIds as $typeId) {
                if (!in_array($typeId, $hidden[$appId] ?? [])) {
                    $rows[] = [
                        'applicationtype_id' => $appId,
                        'attachment_type_id' => $typeId,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                }
            }
        }
        DB::table('applicationtype_attachment_type')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('applicationtype_attachment_type');
    }
};
