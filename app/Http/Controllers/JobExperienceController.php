<?php
// app/Http/Controllers/JobExperienceController.php
namespace App\Http\Controllers;

use App\Models\JobExperience;
use Illuminate\Http\Request;

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
        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'designation' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'details' => 'nullable|string',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        JobExperience::create($data);
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
        return redirect()->route('job_experience.all')->with('success', 'Job experience updated.');
    }

    public function destroy($id)
    {
        $item = JobExperience::findOrFail($id);
        $item->delete();
        return redirect()->route('job_experience.all')->with('success', 'Job experience deleted.');
    }
}
