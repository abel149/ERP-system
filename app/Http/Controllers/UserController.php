<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public const ROLES = ['admin', 'cashier', 'inventory', 'hr', 'production'];

    public function index()
    {
        $users = User::withCount('sales')->latest()->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create', ['roles' => self::ROLES]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create($data);

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return view('users.edit', ['user' => $user, 'roles' => self::ROLES]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($user->isAdmin() && $data['role'] !== 'admin'
            && User::where('role', 'admin')->count() === 1) {
            return back()->withErrors(['role' => 'Cannot demote the last admin.']);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() === 1) {
            return back()->withErrors(['delete' => 'Cannot delete the last admin.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }
}
