<?php
// app/Http/Controllers/JobExperienceController.php
namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\JobExperience;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class JobExperienceController extends Controller
{
    public function index()
    {
        $items = JobExperience::latest()->paginate(20);
        return view('job_experience.index', compact('items'));
    }

    public function create()
    {
        return view('job_experience.create');
    }

    public function store(Request $request)
    {
        $applicant = Applicant::findOrFail($request->applicant_id);

        // ✅ Four Conditions
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return back()->withErrors('Forbidden.');
        }
        if ($applicant->final_submit == 1) {
            return back()->withErrors('Final submission already done. Cannot update.');
        }
        
        //don't delete this code
        /*$setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return back()->withErrors('Submission deadline has passed. Cannot update.');
        }*/

        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && eligibility_approve=0 && payment_status=1)
        if (Auth::user()->user_type === 'applicant') {
            $bypassDeadline =
                ((int)$applicant->final_submit === 0) &&
                ((int)$applicant->admission_approve === 0) &&
                ((int)$applicant->payment_status === 1);

            if (!$bypassDeadline) {
                $setting  = Setting::latest('id')->first();
                $lastDate = $applicant->applicationtype_id == 1 ? ($setting?->end_date) : ($setting?->eligibility_last_date);

                if (!$lastDate) {
                    return response()->json(['message' => 'Setting Table Data Not Found. Contact ICT-CELL.'], 403);
                }

                $deadline = Carbon::parse($lastDate)->endOfDay();
                if (now()->gt($deadline)) {
                    return response()->json(['message' => 'Submission deadline has passed. You cannot upload new files.'], 403);
                }
            }
        }

        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'designation' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'details' => 'nullable|string',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        JobExperience::create($data);
        return redirect()->back()->with('success', 'Job experience created.');
        return redirect()->route('job_experience.all')->with('success', 'Job experience created.');
    }

    public function show($id)
    {
        $item = JobExperience::findOrFail($id);
        return view('job_experience.show', compact('item'));
    }

    public function edit($id)
    {
        $item = JobExperience::findOrFail($id);
        return view('job_experience.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($request->applicant_id);

        // ✅ Four Conditions
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return back()->withErrors('Forbidden.');
        }
        if ($applicant->final_submit == 1) {
            return back()->withErrors('Final submission already done. Cannot update.');
        }
        /*$setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return back()->withErrors('Submission deadline has passed. Cannot update.');
        }*/

        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && eligibility_approve=0 && payment_status=1)
        if (Auth::user()->user_type === 'applicant') {
            $bypassDeadline =
                ((int)$applicant->final_submit === 0) &&
                ((int)$applicant->admission_approve === 0) &&
                ((int)$applicant->payment_status === 1);

            if (!$bypassDeadline) {
                $setting  = Setting::latest('id')->first();
                $lastDate = $applicant->applicationtype_id == 1 ? ($setting?->end_date) : ($setting?->eligibility_last_date);

                if (!$lastDate) {
                    return response()->json(['message' => 'Setting Table Data Not Found. Contact ICT-CELL.'], 403);
                }

                $deadline = Carbon::parse($lastDate)->endOfDay();
                if (now()->gt($deadline)) {
                    return response()->json(['message' => 'Submission deadline has passed. You cannot upload new files.'], 403);
                }
            }
        }

        $item = JobExperience::findOrFail($id);
        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'designation' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'details' => 'nullable|string',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        $item->update($data);
        return redirect()->back()->with('success', 'Job experience updated.');
        return redirect()->route('job_experience.all')->with('success', 'Job experience updated.');
    }

    public function destroy($id)
    {
        $item = JobExperience::findOrFail($id);
        // 2. Load related applicant
        $applicant = Applicant::findOrFail($item->applicant_id);

        // 3. Apply the four conditions
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return back()->withErrors('Forbidden.');
        }

        if ($applicant->final_submit == 1) {
            return back()->withErrors('Final submission already done. Cannot delete.');
        }

        /*$setting = Setting::latest()->first();
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return back()->withErrors('Submission deadline has passed. Cannot delete.');
        }*/

        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && eligibility_approve=0 && payment_status=1)
        if (Auth::user()->user_type === 'applicant') {
            $bypassDeadline =
                ((int)$applicant->final_submit === 0) &&
                ((int)$applicant->admission_approve === 0) &&
                ((int)$applicant->payment_status === 1);

            if (!$bypassDeadline) {
                $setting  = Setting::latest('id')->first();
                $lastDate = $applicant->applicationtype_id == 1 ? ($setting?->end_date) : ($setting?->eligibility_last_date);

                if (!$lastDate) {
                    return response()->json(['message' => 'Setting Table Data Not Found. Contact ICT-CELL.'], 403);
                }

                $deadline = Carbon::parse($lastDate)->endOfDay();
                if (now()->gt($deadline)) {
                    return response()->json(['message' => 'Submission deadline has passed. You cannot upload new files.'], 403);
                }
            }
        }

        $item->delete();
        return redirect()->back()->with('success', 'Job experience deleted.');
        return redirect()->route('job_experience.all')->with('success', 'Job experience deleted.');
    }
}