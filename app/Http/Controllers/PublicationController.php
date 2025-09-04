<?php
// app/Http/Controllers/PublicationController.php
namespace App\Http\Controllers;

use App\Models\Publication;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    public function index()
    {
        $items = Publication::latest()->paginate(20);
        return view('publication.index', compact('items'));
    }

    public function create()
    {
        return view('publication.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'authors' => 'nullable|string|max:255',
            'details' => 'nullable|string',
            'year_of_publication' => 'nullable|integer|min:1900|max:2100',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        Publication::create($data);
        return redirect()->back()->with('success','Publication added successfully');
        return redirect()->route('publication.all')->with('success', 'Publication created.');
    }

    public function show($id)
    {
        $item = Publication::findOrFail($id);
        return view('publication.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Publication::findOrFail($id);
        return view('publication.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Publication::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'authors' => 'nullable|string|max:255',
            'details' => 'nullable|string',
            'year_of_publication' => 'nullable|integer|min:1900|max:2100',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        $item->update($data);
        return redirect()->back()->with('success','Publication updated successfully');
        return redirect()->route('publication.all')->with('success', 'Publication updated.');
    }

    public function destroy($id)
    {
        $item = Publication::findOrFail($id);
        $item->delete();
        return redirect()->back()->with('success','Publication deleted successfully');
        return redirect()->route('publication.all')->with('success', 'Publication deleted.');
    }
}
