<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);

        return view('admin-users', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:mahasiswa,dosen,admin',
            'angkatan' => 'nullable|string',
        ]);

        User::create($request->only(
            'name',
            'email',
            'role',
            'angkatan'
        ));

        return redirect()
            ->back()
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin-users-edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => 'required|in:mahasiswa,dosen,admin',
            'angkatan' => 'nullable|string',
        ]);

        $user->update($request->only(
            'name',
            'email',
            'role',
            'angkatan'
        ));

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return redirect()
            ->back()
            ->with('success', 'User berhasil dihapus.');
    }
}