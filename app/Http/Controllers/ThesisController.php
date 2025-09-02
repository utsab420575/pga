<?php
// app/Http/Controllers/ThesisController.php
namespace App\Http\Controllers;

use App\Models\Thesis;
use Illuminate\Http\Request;

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
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'institute' => 'nullable|string|max:255',
            'period' => 'nullable|string|max:255',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        Thesis::create($data);
        return redirect()->route('thesis.all')->with('success', 'Thesis created.');
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
        $item = Thesis::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'institute' => 'nullable|string|max:255',
            'period' => 'nullable|string|max:255',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        $item->update($data);
        return redirect()->route('thesis.all')->with('success', 'Thesis updated.');
    }

    public function destroy($id)
    {
        $item = Thesis::findOrFail($id);
        $item->delete();
        return redirect()->route('thesis.all')->with('success', 'Thesis deleted.');
    }
}
