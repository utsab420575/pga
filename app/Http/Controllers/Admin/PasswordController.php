<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    const DEFAULT_HEAD_PASSWORD = '12345678';

    public function headIndex()
    {
        $items = User::with('department')
            ->where('user_type', 'head')
            ->orderBy('name')
            ->get();

        return view('admin.settings.passwords.head', compact('items'));
    }

    public function headReset(Request $request)
    {
        $request->validate([
            'mode'  => 'required|in:all,selected',
            'ids'   => 'required_if:mode,selected|array',
            'ids.*' => 'integer',
        ], [
            'ids.required_if' => 'Please select at least one head to reset.',
        ]);

        $query = User::where('user_type', 'head');
        if ($request->mode === 'selected') {
            $query->whereIn('id', $request->ids);
        }

        $count = $query->update([
            'password'   => Hash::make(self::DEFAULT_HEAD_PASSWORD),
            'plain_pass' => self::DEFAULT_HEAD_PASSWORD,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.passwords.head')
            ->with('success', "Password reset to " . self::DEFAULT_HEAD_PASSWORD . " for {$count} head(s).");
    }

    public function applicantIndex(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $items = User::where('user_type', 'applicant')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.settings.passwords.applicant', compact('items', 'search'));
    }
}
