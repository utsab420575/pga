<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use ZipArchive;

class AdmissionApprovalController extends Controller
{
    public function toggle(Request $request, Applicant $applicant)
    {
        // role-based guard: heads can only act on their own department
        $user = auth()->user();

        if ($user->user_type != 'head') {
            abort(403, 'You are not allowed to update this applicant.');
        }

        if ($user->user_type === 'head') {
            if (!$user->department_id || $applicant->department_id !== $user->department_id) {
                abort(403, 'You are not allowed to update this applicant.');
            }
        }

        // optional: only allow toggle if final_submit + payment done
        if ($applicant->final_submit != 1 || $applicant->payment_status != 1) {
            return response()->json(['ok' => false, 'msg' => 'Applicant is not eligible to be approved yet.'], 422);
        }

        // ✅ Date gate: allow ONLY between admission_approval_start_date and admission_approval_last_date
        $setting = Setting::query()->latest('id')->first();
        if (!$setting || !$setting->admission_approval_start_date || !$setting->admission_approval_last_date) {
            return response()->json([
                'ok' => false,
                'msg' => 'Admission approval dates are not configured in Settings. Contact ICT-CELL.'
            ], 422);
        }

        $start = Carbon::parse($setting->admission_approval_start_date)->startOfDay();
        $end   = Carbon::parse($setting->admission_approval_last_date)->endOfDay();
        $now   = now();

        if ($now->lt($start)) {
            return response()->json([
                'ok' => false,
                'msg' => 'Admission approval has not started yet. Window opens on: ' . $start->format('d M Y')
            ], 422);
        }

        if ($now->gt($end)) {
            return response()->json([
                'ok' => false,
                'msg' => 'Admission approval window is closed. Deadline was: ' . $end->format('d M Y')
            ], 422);
        }

        // toggle value
        $applicant->admission_approve = $applicant->admission_approve ? 0 : 1;
        $applicant->save();

        $approved = (int) $applicant->admission_approve === 1;

        return response()->json([
            'ok'       => true,
            'approved' => $approved,
            'label'    => $approved ? 'Undo' : 'Approve Admission',
            'class'    => $approved ? 'btn-danger' : 'btn-success',
            'id'       => $applicant->id,
        ]);
    }



    public function downloadDepartment(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $user = auth()->user();

        // Decide department
        if ($user->user_type === 'head') {
            $deptId = $user->department_id;
            if (!$deptId) abort(403, 'No department set for this head account.');
        } else { // admin must pass dept
            $deptId = (int) $request->query('dept');
            if (!$deptId) abort(400, 'Department id is required.');
        }

        // Pull applicants (Admission only, paid) with relations
        $applicants = Applicant::with([
            'attachments.type',
            'basicInfo',
            'department:id,short_name',
            'user.userAttachments'
        ])
            ->where('department_id', $deptId)
            ->where('applicationtype_id', 1)
            ->where('payment_status', 1)
            ->orderBy('roll')
            ->get();

        if ($applicants->isEmpty()) {
            return back()->with('error', 'No applicants found to download.');
        }

        // Prepare temp dir for zip
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $deptShort = optional($applicants->first()->department)->short_name ?? "DEP{$deptId}";
        $zipName   = 'attachments_' . Str::slug($deptShort, '_') . '_' . now()->format('Ymd_His') . '.zip';
        $zipPath   = $tmpDir . DIRECTORY_SEPARATOR . $zipName;

        // Create ZIP
        $zip = new ZipArchive();
        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($opened !== true) {
            abort(500, 'Could not create ZIP archive (permissions or path).');
        }

        // Helper to resolve absolute path for files from multiple candidate locations
        $resolveFilePath = function (?string $dbPath): ?string {
            if (!$dbPath) return null;
            $rel = ltrim($dbPath, '/\\');
            if (Str::startsWith($rel, 'public/')) {
                $rel = substr($rel, 7);
            }

            $candidates = [
                public_path($rel),
                storage_path('app/public/' . $rel),
                storage_path('app/' . $rel),
                base_path($rel),
            ];

            foreach ($candidates as $cand) {
                if (is_file($cand)) {
                    return $cand;
                }
            }

            return null;
        };

        foreach ($applicants as $a) {
            // Folder name per applicant: Roll_Name or APP_ID_Name
            $rollPart = $a->roll ? $a->roll : 'APP_' . $a->id;
            $namePart = optional($a->user)->name ? '_' . Str::slug($a->user->name, '_') : '';
            $folder   = $rollPart . $namePart;

            $usedNames = [];
            $addedAbsPaths = [];

            // 1) All documents from attachments table
            foreach ($a->attachments as $att) {
                if (!$att->file) continue;

                $abs = $resolveFilePath($att->file);
                if (!$abs) continue;

                $real = realpath($abs) ?: $abs;
                if (isset($addedAbsPaths[$real])) continue;
                $addedAbsPaths[$real] = true;

                // Attachment type title
                $typeTitle = optional($att->type)->title ?: ($att->title ?: 'Document');
                $cleanType = Str::slug(trim(preg_replace('/[\r\n\t]+/', ' ', $typeTitle)), '_');
                if (empty($cleanType)) {
                    $cleanType = 'document';
                }
                $cleanType = substr($cleanType, 0, 70);

                $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION) ?: 'pdf');
                $cleanName = $cleanType . '.' . $ext;

                if (isset($usedNames[$cleanName])) {
                    $usedNames[$cleanName]++;
                    $cleanName = $cleanType . '_' . $usedNames[$cleanName] . '.' . $ext;
                } else {
                    $usedNames[$cleanName] = 1;
                }

                $zip->addFile($abs, $folder . '/' . $cleanName);
            }

            // 2) Documents from basic_info (photo & sign if not already attached)
            if ($a->basicInfo) {
                if ($a->basicInfo->photo) {
                    $abs = $resolveFilePath($a->basicInfo->photo);
                    if ($abs) {
                        $real = realpath($abs) ?: $abs;
                        if (!isset($addedAbsPaths[$real])) {
                            $addedAbsPaths[$real] = true;
                            $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION) ?: 'jpg');
                            $cleanName = 'recent_picture.' . $ext;
                            if (isset($usedNames[$cleanName])) {
                                $usedNames[$cleanName]++;
                                $cleanName = 'recent_picture_' . $usedNames[$cleanName] . '.' . $ext;
                            } else {
                                $usedNames[$cleanName] = 1;
                            }
                            $zip->addFile($abs, $folder . '/' . $cleanName);
                        }
                    }
                }

                if ($a->basicInfo->sign) {
                    $abs = $resolveFilePath($a->basicInfo->sign);
                    if ($abs) {
                        $real = realpath($abs) ?: $abs;
                        if (!isset($addedAbsPaths[$real])) {
                            $addedAbsPaths[$real] = true;
                            $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION) ?: 'jpg');
                            $cleanName = 'signature.' . $ext;
                            if (isset($usedNames[$cleanName])) {
                                $usedNames[$cleanName]++;
                                $cleanName = 'signature_' . $usedNames[$cleanName] . '.' . $ext;
                            } else {
                                $usedNames[$cleanName] = 1;
                            }
                            $zip->addFile($abs, $folder . '/' . $cleanName);
                        }
                    }
                }
            }

            // 3) Documents from user_attachments (e.g. previous eligibility proof)
            if ($a->user && $a->user->userAttachments) {
                foreach ($a->user->userAttachments as $ua) {
                    if (!$ua->file) continue;

                    $abs = $resolveFilePath($ua->file);
                    if (!$abs) continue;

                    $real = realpath($abs) ?: $abs;
                    if (isset($addedAbsPaths[$real])) continue;
                    $addedAbsPaths[$real] = true;

                    $title = $ua->title ?: ($ua->attachments ?: 'Previously Approved Eligibility');
                    $cleanType = Str::slug(trim(preg_replace('/[\r\n\t]+/', ' ', $title)), '_');
                    if (empty($cleanType)) {
                        $cleanType = 'user_document';
                    }
                    $cleanType = substr($cleanType, 0, 70);

                    $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION) ?: 'pdf');
                    $cleanName = $cleanType . '.' . $ext;

                    if (isset($usedNames[$cleanName])) {
                        $usedNames[$cleanName]++;
                        $cleanName = $cleanType . '_' . $usedNames[$cleanName] . '.' . $ext;
                    } else {
                        $usedNames[$cleanName] = 1;
                    }

                    $zip->addFile($abs, $folder . '/' . $cleanName);
                }
            }
        }

        if ($zip->numFiles === 0) {
            $zip->close();
            @unlink($zipPath);
            return back()->with('error', 'No physical attachment files found on server for this department.');
        }

        $zip->close();

        if (!is_file($zipPath)) {
            abort(500, 'ZIP was not created. Check storage/app/tmp permissions.');
        }

        // Stream and delete after send
        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }




    //excel file download
    public function exportDepartmentInfo(Request $request)
    {
        $user = auth()->user();

        // Determine department
        if ($user->user_type === 'head') {
            $deptId = $user->department_id;
            if (!$deptId) abort(403, 'No department set for this head account.');
        } else {
            $deptId = (int) $request->query('dept');
            if (!$deptId) abort(400, 'Department id is required.');
        }

        // Pull data (Admission only, paid)
        $rows = Applicant::with([
            'department:id,short_name',
            'degree:id,degree_name',
            'user:id,name,phone',
            'payment:paymentdate,applicant_id',
            'basicInfo:id,applicant_id,f_name',
        ])
            ->where('department_id', $deptId)
            ->where('applicationtype_id', 1)
            ->where('payment_status', 1)
            ->orderBy('roll')
            ->get();

        if ($rows->isEmpty()) {
            return back()->with('error', 'No applicants to export.');
        }

        $deptShort = optional($rows->first()->department)->short_name ?? "DEP{$deptId}";
        $filename  = 'applicants_' . \Illuminate\Support\Str::slug($deptShort, '_') . '_' . now()->format('Ymd_His') . '.csv';

        // Stream CSV so it’s memory-light
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            // Excel UTF-8 BOM (Windows-friendly)
            fwrite($out, "\xEF\xBB\xBF");

            // Headings
            fputcsv($out, ['Roll','Department','Degree','Name','Father Name','Payment Date','Mobile']);

            foreach ($rows as $a) {
                $roll    = $a->roll;
                $dept    = optional($a->department)->short_name;
                $degree  = optional($a->degree)->degree_name;
                $name    = optional($a->user)->name;
                $father  = optional($a->basicInfo)->f_name;
                $payDate = optional($a->payment)->paymentdate ? \Carbon\Carbon::parse($a->payment->paymentdate)->toDateString() : '';
                $mobile  = optional($a->user)->phone;

                fputcsv($out, [$roll,$dept,$degree,$name,$father,$payDate,$mobile]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}