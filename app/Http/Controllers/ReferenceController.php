<?php
// app/Http/Controllers/ReferenceController.php
namespace App\Http\Controllers;

use App\Models\Reference;
use Illuminate\Http\Request;

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
        return redirect()->route('reference.all')->with('success', 'Reference updated.');
    }

    public function destroy($id)
    {
        $item = Reference::findOrFail($id);
        $item->delete();
        return redirect()->route('reference.all')->with('success', 'Reference deleted.');
    }
}
