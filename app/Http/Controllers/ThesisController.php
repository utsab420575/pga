<?php
// app/Http/Controllers/ThesisController.php
namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Setting;
use App\Models\Thesis;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ThesisController extends Controller
{
    public function index()
    {
        $items = Thesis::latest()->paginate(20);
        return view('thesis.index', compact('items'));
    }

    public function create()
    {
        return view('thesis.create');
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
        
        /*$setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return back()->withErrors('Submission deadline has passed. Cannot update.');
        }*/

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
            'title' => 'required|string|max:255',
            'institute' => 'nullable|string|max:255',
            'supervisor' => 'nullable|string|max:255',
            'period' => 'nullable|string|max:255',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        Thesis::create($data);
        return redirect()->back()->with('success','Thesis added successfully');
        //return redirect()->route('thesis.all')->with('success', 'Thesis created.');
    }

    public function show($id)
    {
        $item = Thesis::findOrFail($id);
        return view('thesis.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Thesis::findOrFail($id);
        return view('thesis.edit', compact('item'));
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

        $item = Thesis::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'institute' => 'nullable|string|max:255',
            'period' => 'nullable|string|max:255',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        $item->update($data);
        return redirect()->back()->with('success','Thesis updated successfully');
        //return redirect()->route('thesis.all')->with('success', 'Thesis updated.');
    }

    public function destroy($id)
    {
        $item = Thesis::findOrFail($id);

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
        return redirect()->back()->with('success','Thesis deleted successfully');
        return redirect()->route('thesis.all')->with('success', 'Thesis deleted.');
    }
}