<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    use LogsActivity;
    public function index()
    {
        $users = User::latest()->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:super_admin,hr,manager,staff'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->logActivity('user', 'Create', 'Menambahkan user: ' . $request->name, null, null, null, $request->only('name', 'email', 'role', 'phone'));

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class . ',email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:super_admin,hr,manager,staff'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $old = $user->only('name', 'email', 'role', 'phone', 'is_active');
        $user->update($data);

        $this->logActivity('user', 'Update', 'Mengubah user: ' . $user->name, 'User', $user->id, $old, $user->only('name', 'email', 'role', 'phone', 'is_active'));

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $name = $user->name;
        $user->delete();

        $this->logActivity('user', 'Delete', 'Menghapus user: ' . $name, 'User', $user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
