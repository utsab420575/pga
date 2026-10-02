<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which attachment types are required, per application type and (optionally) per degree.
     * A row = required. degree_id NULL = required for every degree. No row = optional.
     */
    public function up(): void
    {
        Schema::create('attachment_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicationtype_id')->constrained('applicationtypes')->cascadeOnDelete();
            $table->foreignId('attachment_type_id')->constrained('attachment_types')->cascadeOnDelete();
            $table->foreignId('degree_id')->nullable()->constrained('degrees')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['applicationtype_id', 'attachment_type_id', 'degree_id'], 'attachment_requirements_unique');
        });

        // Seed with the rules previously hardcoded in FinalSubmitController
        $admission   = 1;
        $eligibility = 2;
        $phd         = 8;

        $rules = [
            // [applicationtype_id, attachment_type_ids, degree_id]
            [$admission,   [1, 2, 3, 4, 5, 7, 8, 9, 11],      null],
            [$admission,   [6, 10],                           $phd],
            [$eligibility, [1, 2, 3, 4, 5, 7, 8, 9, 13, 14],  null],
            [$eligibility, [6, 10],                           $phd],
        ];

        $existingTypes   = DB::table('attachment_types')->pluck('id')->all();
        $existingDegrees = DB::table('degrees')->pluck('id')->all();
        $existingApps    = DB::table('applicationtypes')->pluck('id')->all();

        $rows = [];
        foreach ($rules as [$appType, $typeIds, $degreeId]) {
            if (!in_array($appType, $existingApps) || ($degreeId && !in_array($degreeId, $existingDegrees))) {
                continue;
            }
            foreach ($typeIds as $typeId) {
                if (in_array($typeId, $existingTypes)) {
                    $rows[] = [
                        'applicationtype_id' => $appType,
                        'attachment_type_id' => $typeId,
                        'degree_id'          => $degreeId,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                }
            }
        }
        DB::table('attachment_requirements')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('attachment_requirements');
    }
};
