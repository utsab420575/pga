<?php
// app/Http/Controllers/EducationInfoController.php
namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\EducationInfo;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EducationInfoController extends Controller
{
    public function index()
    {
        $items = EducationInfo::latest()->paginate(20);
        return view('education_info.index', compact('items'));
    }

    public function create()
    {
        return view('education_info.create');
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
        $setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return back()->withErrors('Submission deadline has passed. Cannot update.');
        }

        $data = $request->validate([
            'degree' => 'required|string|max:255',
            'institute' => 'required|string|max:255',
            'year_of_passing' => 'nullable|integer|min:1900|max:2100',
            'field' => 'nullable|string|max:255',
            'cgpa' => 'nullable|string|max:255',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        EducationInfo::create($data);
        return redirect()->back()->with('success', 'Education Information Added');
    }

    public function show($id)
    {
        $item = \App\Models\EducationInfo::findOrFail($id);

        // (optional) authorize that the logged-in user can see this row

        return response()->json([
            'id'              => $item->id,
            'degree'          => $item->degree,
            'institute'       => $item->institute,
            'year_of_passing' => $item->year_of_passing,
            'field'           => $item->field,
            'cgpa'            => $item->cgpa,
            'applicant_id'    => $item->applicant_id,
        ]);
    }

    public function edit($id)
    {
        $item = EducationInfo::findOrFail($id);
        return view('education_info.edit', compact('item'));
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
        $setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return back()->withErrors('Submission deadline has passed. Cannot update.');
        }

        $item = EducationInfo::findOrFail($id);
        $data = $request->validate([
            'degree' => 'required|string|max:255',
            'institute' => 'required|string|max:255',
            'year_of_passing' => 'nullable|integer|min:1900|max:2100',
            'field' => 'nullable|string|max:255',
            'cgpa' => 'nullable|string|max:255',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        $item->update($data);
        return redirect()->back()->with('success', 'Education Information Updated');
    }

    public function destroy($id)
    {
        $item = EducationInfo::findOrFail($id);
        // 2. Load related applicant
        $applicant = Applicant::findOrFail($item->applicant_id);

        // 3. Apply the four conditions
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return back()->withErrors('Forbidden.');
        }

        if ($applicant->final_submit == 1) {
            return back()->withErrors('Final submission already done. Cannot delete.');
        }

        $setting = Setting::latest()->first();
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return back()->withErrors('Submission deadline has passed. Cannot delete.');
        }

        $item->delete();
        return redirect()->back()->with('success','Education Information Deleted successfully');
    }
}