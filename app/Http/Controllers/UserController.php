<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:30', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:100'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['owner', 'admin', 'kasir'])],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:30', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['owner', 'admin', 'kasir'])],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
