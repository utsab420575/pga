<?php
// app/Http/Controllers/SettingController.php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $items = Setting::latest()->paginate(20);
        return view('setting.index', compact('items'));
    }

    public function create()
    {
        return view('setting.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'session' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'last_payment_date' => 'nullable|date',
            'eligibility_last_date' => 'nullable|date',
        ]);

        Setting::create($data);
        return redirect()->route('setting.all')->with('success', 'Setting created.');
    }

    public function show($id)
    {
        $item = Setting::findOrFail($id);
        return view('setting.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Setting::findOrFail($id);
        return view('setting.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Setting::findOrFail($id);
        $data = $request->validate([
            'session' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'last_payment_date' => 'nullable|date',
            'eligibility_last_date' => 'nullable|date',
        ]);

        $item->update($data);
        return redirect()->route('setting.all')->with('success', 'Setting updated.');
    }

    public function destroy($id)
    {
        $item = Setting::findOrFail($id);
        $item->delete();
        return redirect()->route('setting.all')->with('success', 'Setting deleted.');
    }
}
