<?php
// app/Http/Controllers/EligibilityDegreeController.php
namespace App\Http\Controllers;

use App\Models\EligibilityDegree;
use Illuminate\Http\Request;

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
        return redirect()->route('eligibility_degree.all')->with('success', 'Eligibility degree created.');
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
        return redirect()->route('eligibility_degree.all')->with('success', 'Eligibility degree updated.');
    }

    public function destroy($id)
    {
        $item = EligibilityDegree::findOrFail($id);
        $item->delete();
        return redirect()->route('eligibility_degree.all')->with('success', 'Eligibility degree deleted.');
    }
}
