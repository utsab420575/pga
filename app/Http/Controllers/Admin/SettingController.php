<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $items = Setting::latest()->paginate(20);
        return view('admin.settings.settings.index', compact('items'));
    }

    public function create()
    {
        return view('admin.settings.settings.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'session'                      => 'nullable|string|max:255',
            'start_date'                   => 'nullable|date',
            'end_date'                     => 'nullable|date|after_or_equal:start_date',
            'last_payment_date'            => 'nullable|date',
            'eligibility_start_date'          => 'nullable|date',
            'eligibility_last_date'           => 'nullable|date',
            'last_eligibility_payment_date'   => 'nullable|date',
            'eligibility_approval_start_date' => 'nullable|date',
            'eligibility_approval_last_date'  => 'nullable|date|after_or_equal:eligibility_approval_start_date',
            'admission_approval_start_date'   => 'nullable|date',
            'admission_approval_last_date'    => 'nullable|date|after_or_equal:admission_approval_start_date',
            'sms_api'                         => 'nullable|string',
            'google_auth_api'                 => 'nullable|string',
        ]);

        Setting::create($data);
        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting created successfully.');
    }

    public function edit($id)
    {
        $item = Setting::findOrFail($id);
        return view('admin.settings.settings.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Setting::findOrFail($id);
        $data = $request->validate([
            'session'                      => 'nullable|string|max:255',
            'start_date'                   => 'nullable|date',
            'end_date'                     => 'nullable|date|after_or_equal:start_date',
            'last_payment_date'            => 'nullable|date',
            'eligibility_start_date'          => 'nullable|date',
            'eligibility_last_date'           => 'nullable|date',
            'last_eligibility_payment_date'   => 'nullable|date',
            'eligibility_approval_start_date' => 'nullable|date',
            'eligibility_approval_last_date'  => 'nullable|date|after_or_equal:eligibility_approval_start_date',
            'admission_approval_start_date'   => 'nullable|date',
            'admission_approval_last_date'    => 'nullable|date|after_or_equal:admission_approval_start_date',
            'sms_api'                         => 'nullable|string',
            'google_auth_api'                 => 'nullable|string',
        ]);

        $item->update($data);
        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    public function destroy($id)
    {
        $item = Setting::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting deleted successfully.');
    }
}
