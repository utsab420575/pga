<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicationtype;
use App\Models\AttachmentRequirement;
use App\Models\AttachmentType;
use App\Models\Degree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttachmentRequirementController extends Controller
{
    public function index(Request $request)
    {
        $applicationtypes = Applicationtype::orderBy('id')->get();
        $current = $applicationtypes->firstWhere('id', (int) $request->get('applicationtype_id')) ?? $applicationtypes->first();

        // attachment_type_id => [degree_id|null, ...]
        $rules = AttachmentRequirement::where('applicationtype_id', $current?->id)
            ->get(['attachment_type_id', 'degree_id'])
            ->groupBy('attachment_type_id')
            ->map(fn ($rows) => $rows->pluck('degree_id')->all());

        $offered = DB::table('applicationtype_attachment_type')
            ->where('applicationtype_id', $current?->id)
            ->pluck('attachment_type_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return view('admin.settings.attachment_requirements.index', [
            'applicationtypes' => $applicationtypes,
            'current'          => $current,
            'attachmentTypes'  => AttachmentType::orderBy('id')->get(),
            'degrees'          => Degree::orderBy('id')->get(),
            'rules'            => $rules,
            'offered'          => $offered,
        ]);
    }

    public function update(Request $request, $applicationtypeId)
    {
        $applicationtype = Applicationtype::findOrFail($applicationtypeId);

        $data = $request->validate([
            'req'             => 'array',
            'req.*.mode'      => 'required|in:none,optional,all,selected',
            'req.*.degrees'   => 'array',
            'req.*.degrees.*' => 'integer|exists:degrees,id',
        ]);

        $validTypeIds = AttachmentType::pluck('id')->all();
        $rows = [];        // attachment_requirements
        $offeredRows = []; // applicationtype_attachment_type

        foreach ($data['req'] ?? [] as $typeId => $req) {
            if (!in_array((int) $typeId, $validTypeIds) || $req['mode'] === 'none') {
                continue; // not offered = no row in either table
            }

            // Optional and required types are both offered in the upload dropdown
            $offeredRows[] = [
                'applicationtype_id' => $applicationtype->id,
                'attachment_type_id' => (int) $typeId,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];

            if ($req['mode'] === 'all') {
                $degreeIds = [null];
            } elseif ($req['mode'] === 'selected') {
                $degreeIds = array_unique(array_map('intval', $req['degrees'] ?? []));
                if (empty($degreeIds)) {
                    $title = trim(AttachmentType::find($typeId)->title);
                    throw ValidationException::withMessages([
                        "req.$typeId.degrees" => "Select at least one degree for \"{$title}\", or choose another option.",
                    ]);
                }
            } else {
                continue; // optional = offered, but no requirement row
            }

            foreach ($degreeIds as $degreeId) {
                $rows[] = [
                    'applicationtype_id' => $applicationtype->id,
                    'attachment_type_id' => (int) $typeId,
                    'degree_id'          => $degreeId,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }
        }

        DB::transaction(function () use ($applicationtype, $rows, $offeredRows) {
            AttachmentRequirement::where('applicationtype_id', $applicationtype->id)->delete();
            AttachmentRequirement::insert($rows);

            DB::table('applicationtype_attachment_type')->where('applicationtype_id', $applicationtype->id)->delete();
            DB::table('applicationtype_attachment_type')->insert($offeredRows);
        });

        return redirect()->route('admin.attachment_requirements.index', ['applicationtype_id' => $applicationtype->id])
            ->with('success', "Required attachments saved for {$applicationtype->type}.");
    }
}
