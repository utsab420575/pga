<?php
// app/Http/Controllers/BasicInfoController.php
namespace App\Http\Controllers;

use App\Models\BasicInfo;
use Illuminate\Http\Request;

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
        $data = $request->validate([
            'bn_name' => 'nullable|string|max:255',
            'f_name' => 'nullable|string|max:255',
            'm_name' => 'nullable|string|max:255',
            'g_incode' => 'nullable|string|max:255',
            'passport_no' => 'nullable|string|max:255',
            'per_address' => 'nullable|string',
            'pre_address' => 'nullable|string',
            'dob' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'nid' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:50',
            'marital_status' => 'nullable|string|max:50',
            'field_of_interest' => 'nullable|string|max:255',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'sign'  => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('basic_info/photos', 'public');
        }
        if ($request->hasFile('sign')) {
            $data['sign'] = $request->file('sign')->store('basic_info/signs', 'public');
        }

        BasicInfo::create($data);
        return redirect()->route('basic_info.all')->with('success', 'Basic info created.');
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
        $item = BasicInfo::findOrFail($id);
        $data = $request->validate([
            'bn_name' => 'nullable|string|max:255',
            'f_name' => 'nullable|string|max:255',
            'm_name' => 'nullable|string|max:255',
            'g_incode' => 'nullable|string|max:255',
            'passport_no' => 'nullable|string|max:255',
            'per_address' => 'nullable|string',
            'pre_address' => 'nullable|string',
            'dob' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'nid' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:50',
            'marital_status' => 'nullable|string|max:50',
            'field_of_interest' => 'nullable|string|max:255',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'sign'  => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('basic_info/photos', 'public');
        }
        if ($request->hasFile('sign')) {
            $data['sign'] = $request->file('sign')->store('basic_info/signs', 'public');
        }

        $item->update($data);
        return redirect()->route('basic_info.all')->with('success', 'Basic info updated.');
    }

    public function destroy($id)
    {
        $item = BasicInfo::findOrFail($id);
        $item->delete();
        return redirect()->route('basic_info.all')->with('success', 'Basic info deleted.');
    }
}
