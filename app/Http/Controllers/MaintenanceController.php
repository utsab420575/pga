<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\BasicInfo;

use Illuminate\Support\Facades\DB;
class MaintenanceController extends Controller
{
    public function backfillPhotoSign(Request $request)
    {
        //return 'hi';
        $updated = 0;
        $created = 0; // number of BasicInfo rows we had to create
        $skipped = 0;

        // Process in chunks to avoid memory spikes
        try {
            DB::beginTransaction();

            Applicant::with([
                // Only the two types we care about; order stable so "first" is consistent
                'attachments' => function ($q) {
                    $q->whereIn('attachment_type_id', [1, 2])->orderBy('id');
                },
                'basicInfo',
            ])->chunkById(200, function ($chunk) use (&$updated, &$created, &$skipped) {
                foreach ($chunk as $applicant) {
                    $bi = $applicant->basicInfo;

                    if (!$bi) {
                        continue;
                        $bi = new BasicInfo();
                        $bi->applicant_id = $applicant->id;
                        $created++;
                    }

                    $changed = false;

                    $photoAtt = $applicant->attachments->firstWhere('attachment_type_id', 1);
                    $signAtt  = $applicant->attachments->firstWhere('attachment_type_id', 2);

                    if (empty($bi->photo) && $photoAtt && !empty($photoAtt->file)) {
                        $bi->photo = $photoAtt->file;   // DB already stores relative path (e.g., public/attachments/...)
                        $changed = true;
                    }

                    if (empty($bi->sign) && $signAtt && !empty($signAtt->file)) {
                        $bi->sign = $signAtt->file;     // column name is 'sign' in your schema
                        $changed = true;
                    }

                    if ($changed) {
                        $bi->save();
                        $updated++;
                    } else {
                        $skipped++;
                    }
                }
            });

            DB::commit();

            return response()->json([
                'ok'       => true,
                'updated'  => $updated,
                'created_basicinfo_rows' => $created,
                'skipped'  => $skipped,
                'message'  => "Backfill completed: updated={$updated}, created_basicinfo_rows={$created}, skipped={$skipped}",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
