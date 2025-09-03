<?php
// app/Http/Controllers/EducationInfoController.php
namespace App\Http\Controllers;

use App\Models\EducationInfo;
use Illuminate\Http\Request;

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
        $data = $request->validate([
            'degree' => 'required|string|max:255',
            'institute' => 'required|string|max:255',
            'year_of_passing' => 'nullable|integer|min:1900|max:2100',
            'field' => 'nullable|string|max:255',
            'cgpa' => 'nullable|numeric|min:0|max:5',
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
        $item = EducationInfo::findOrFail($id);
        $data = $request->validate([
            'degree' => 'required|string|max:255',
            'institute' => 'required|string|max:255',
            'year_of_passing' => 'nullable|integer|min:1900|max:2100',
            'field' => 'nullable|string|max:255',
            'cgpa' => 'nullable|numeric|min:0|max:5',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        $item->update($data);
        return redirect()->back()->with('success', 'Education Information Updated');
    }

    public function destroy($id)
    {
        $item = EducationInfo::findOrFail($id);
        $item->delete();
        return redirect()->back()->with('success','Education Information Deleted successfully');
    }
}
