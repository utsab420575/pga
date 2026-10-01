<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    const USER_TYPES = ['admin', 'head', 'applicant'];

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $type   = $request->get('user_type');

        $items = User::with('department')
            ->when(in_array($type, self::USER_TYPES, true), fn ($q) => $q->where('user_type', $type))
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

        return view('admin.settings.users.index', [
            'items'     => $items,
            'search'    => $search,
            'type'      => $type,
            'userTypes' => self::USER_TYPES,
        ]);
    }

    public function create()
    {
        return view('admin.settings.users.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $user = new User();
        $this->fill($user, $data);
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" created successfully.");
    }

    public function edit($id)
    {
        $item = User::findOrFail($id);
        return view('admin.settings.users.edit', $this->formData() + compact('item'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $this->validated($request, $user);

        $this->fill($user, $data);
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" updated successfully.");
    }

    protected function formData(): array
    {
        return [
            'userTypes'   => self::USER_TYPES,
            'departments' => Department::orderBy('short_name')->get(['id', 'short_name', 'full_name']),
        ];
    }

    protected function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone'          => 'nullable|string|max:20',
            'phone_verified' => 'required|boolean',
            'user_type'      => ['required', Rule::in(self::USER_TYPES)],
            'department_id'  => 'nullable|required_if:user_type,head|exists:departments,id',
            'email_verified' => 'nullable|boolean',
            // Required on create; on edit leave blank to keep the current password
            'password'       => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ], [
            'department_id.required_if' => 'Department is required for head users.',
        ]);
    }

    protected function fill(User $user, array $data): void
    {
        $user->name           = $data['name'];
        $user->email          = $data['email'];
        $user->phone          = $data['phone'] ?? null;
        $user->phone_verified = (int) $data['phone_verified'];
        $user->user_type      = $data['user_type'];
        $user->department_id  = $data['department_id'] ?? null;

        if (!empty($data['email_verified'])) {
            $user->email_verified_at ??= now();
        } else {
            $user->email_verified_at = null;
        }

        if (!empty($data['password'])) {
            $user->password   = Hash::make($data['password']);
            $user->plain_pass = $data['password'];
        }
    }
}
