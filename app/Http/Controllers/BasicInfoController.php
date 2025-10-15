<?php
// app/Http/Controllers/BasicInfoController.php
namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\BasicInfo;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BasicInfoController extends Controller
{
    public function index()
    {
        $items = BasicInfo::latest()->paginate(20);
        return view('basic_info.index', compact('items'));
    }

    public function create()
    {
        return view('basic_info.create');
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
        /* $setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return response()->json(['message' => 'Submission deadline has passed. You cannot delete files.'], 403);
        }*/

        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && eligibility_approve=0 && payment_status=1)
        if (Auth::user()->user_type === 'applicant') {
            $bypassDeadline =
                ((int)$applicant->final_submit === 0) &&
                ((int)$applicant->eligibility_approve === 0) &&
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

        // ✅ Base rules
        $rules = [
            'full_name_block_letter' => 'required|string|max:255',
            'f_name'          => 'required|string|max:255',
            'm_name'          => 'required|string|max:255',
            'nationality'     => 'required|string|max:100',
            'dob'             => 'required|date',
            'religion'        => 'required|in:Islam,Hindu,Cristan,Baudda,Others',
            'gender'          => 'required|in:Male,Female,Other',
            'marital_status'  => 'required|in:Single,Married,Divorced,Widowed',
            'full_name'       => 'nullable|string|max:255',
            'bn_name'         => 'nullable|string|max:255',
            'g_income'        => ['nullable', 'numeric', 'decimal:0,2', 'between:0,99999999.99'],
            'passport_no'     => 'nullable|string|max:255',
            'per_address'     => 'nullable|string',
            'pre_address'     => 'nullable|string',
            'nid'             => 'nullable|string|max:100',
            'field_of_interest' => 'nullable|string|max:255',
            'photo'           => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'sign'            => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'applicant_id'    => 'required|exists:applicants,id',
        ];

        // ✅ Extra validation only for CE department
        if ($applicant->department_id == 1 && $applicant->applicationtype_id==1) {
            $rules['field_name_ce'] = 'required|string|max:255';
        }

        $data = $request->validate($rules);

        // ✅ Handle file uploads
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('basic_info/photos', 'public');
        }
        if ($request->hasFile('sign')) {
            $data['sign'] = $request->file('sign')->store('basic_info/signs', 'public');
        }

        // ✅ Ensure CE-only field is null for other departments
        if ($applicant->department_id != 1) {
            $data['field_name_ce'] = null;
        }

        BasicInfo::create($data);

        return redirect()->back()->with('success', 'Basic info created successfully.');
    }


    public function show($id)
    {
        $item = BasicInfo::findOrFail($id);
        return view('basic_info.show', compact('item'));
    }

    public function edit($id)
    {
        $item = BasicInfo::findOrFail($id);
        return view('basic_info.edit', compact('item'));
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
        /* $setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return response()->json(['message' => 'Submission deadline has passed. You cannot delete files.'], 403);
        }*/

        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && eligibility_approve=0 && payment_status=1)
        if (Auth::user()->user_type === 'applicant') {
            $bypassDeadline =
                ((int)$applicant->final_submit === 0) &&
                ((int)$applicant->eligibility_approve === 0) &&
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

        $item = BasicInfo::findOrFail($id);

        // ✅ Base validation rules
        $rules = [
            'full_name_block_letter' => 'required|string|max:255',
            'f_name'          => 'required|string|max:255',
            'm_name'          => 'required|string|max:255',
            'nationality'     => 'required|string|max:100',
            'dob'             => 'required|date',
            'religion'        => 'required|in:Islam,Hindu,Cristan,Baudda,others',
            'gender'          => 'required|in:Male,Female,Other',
            'marital_status'  => 'required|in:Single,Married,Divorced,Widowed',
            'full_name'       => 'nullable|string|max:255',
            'bn_name'         => 'nullable|string|max:255',
            'g_income'        => ['nullable', 'numeric', 'decimal:0,2', 'between:0,99999999.99'],
            'passport_no'     => 'nullable|string|max:255',
            'per_address'     => 'nullable|string',
            'pre_address'     => 'nullable|string',
            'nid'             => 'nullable|string|max:100',
            'field_of_interest' => 'nullable|string|max:255',
            'photo'           => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'sign'            => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'applicant_id'    => 'required|exists:applicants,id',
        ];

        // ✅ Extra rule only for CE (Civil Engineering)
        if ($applicant->department_id == 1 && $applicant->applicationtype_id==1) {
            $rules['field_name_ce'] = 'required|string|max:255';
        }

        $data = $request->validate($rules);

        // ✅ Handle file uploads
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('basic_info/photos', 'public');
        }
        if ($request->hasFile('sign')) {
            $data['sign'] = $request->file('sign')->store('basic_info/signs', 'public');
        }

        $item->update($data);

        return redirect()->back()->with('success', 'Basic info updated.');
    }


    public function destroy($id)
    {
        $item = BasicInfo::findOrFail($id);
        // 2. Load related applicant
        $applicant = Applicant::findOrFail($item->applicant_id);

        // 3. Apply the four conditions
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return back()->withErrors('Forbidden.');
        }

        if ($applicant->final_submit == 1) {
            return back()->withErrors('Final submission already done. Cannot delete.');
        }

        /* $setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return response()->json(['message' => 'Submission deadline has passed. You cannot delete files.'], 403);
        }*/

        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && eligibility_approve=0 && payment_status=1)
        if (Auth::user()->user_type === 'applicant') {
            $bypassDeadline =
                ((int)$applicant->final_submit === 0) &&
                ((int)$applicant->eligibility_approve === 0) &&
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
        return redirect()->route('basic_info.all')->with('success', 'Basic info deleted.');
    }
}