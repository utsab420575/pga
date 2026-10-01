<?php
// app/Http/Controllers/ReferenceController.php
namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Reference;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ReferenceController extends Controller
{
    public function index()
    {
        $items = Reference::latest()->paginate(20);
        return view('reference.index', compact('items'));
    }

    public function create()
    {
        return view('reference.create');
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
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'institute' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'order_no' => 'nullable|integer|min:1',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        Reference::create($data);
        return redirect()->back()->with('success','Reference created successfully');
        return redirect()->route('reference.all')->with('success', 'Reference created.');
    }

    public function show($id)
    {
        $item = Reference::findOrFail($id);
        return view('reference.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Reference::findOrFail($id);
        return view('reference.edit', compact('item'));
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

        $item = Reference::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'institute' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'order_no' => 'nullable|integer|min:1',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        $item->update($data);
        return redirect()->back()->with('success','Reference updated successfully');
        return redirect()->route('reference.all')->with('success', 'Reference updated.');
    }

    public function destroy($id)
    {
        $item = Reference::findOrFail($id);

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
        return redirect()->back()->with('success','Reference deleted successfully');
        return redirect()->route('reference.all')->with('success', 'Reference deleted.');
    }
}