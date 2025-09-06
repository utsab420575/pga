<?php
// app/Http/Controllers/EligibilityDegreeController.php
namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\EligibilityDegree;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EligibilityDegreeController extends Controller
{
    public function index()
    {
        $items = EligibilityDegree::latest()->paginate(20);
        return view('eligibility_degree.index', compact('items'));
    }

    public function create()
    {
        return view('eligibility_degree.create');
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
            'institute' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'cgpa' => 'nullable|numeric|min:0|max:5',
            'date_graduation' => 'nullable|date',
            'duration' => 'nullable|string|max:255',
            'total_credit' => 'nullable|numeric|min:0|max:999.99',
            'mode' => 'nullable|string|max:255',
            'period' => 'nullable|string|max:255',
            'uni_status' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:2048',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        EligibilityDegree::create($data);
        return redirect()->back()->with('success', 'Eligibility Degree Created.');
    }

    public function show($id)
    {
        $item = EligibilityDegree::findOrFail($id);
        return view('eligibility_degree.show', compact('item'));
    }

    public function edit($id)
    {
        $item = EligibilityDegree::findOrFail($id);
        return view('eligibility_degree.edit', compact('item'));
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


        $item = EligibilityDegree::findOrFail($id);
        $data = $request->validate([
            'degree' => 'required|string|max:255',
            'institute' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'cgpa' => 'nullable|numeric|min:0|max:5',
            'date_graduation' => 'nullable|date',
            'duration' => 'nullable|string|max:255',
            'total_credit' => 'nullable|numeric|min:0|max:999.99',
            'mode' => 'nullable|string|max:255',
            'period' => 'nullable|string|max:255',
            'uni_status' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:2048',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        $item->update($data);
        return redirect()->back()->with('success', 'Eligibility Degree Updated.');
    }

    public function destroy($id)
    {
        $item = EligibilityDegree::findOrFail($id);
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
        return redirect()->route('eligibility_degree.all')->with('success', 'Eligibility degree deleted.');
    }
}
