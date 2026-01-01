<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AkunRoleController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('role', 'like', "%{$search}%");
        })->paginate(10);

        return view('akunrole.index', compact('users'));
    }

    /**
     * Form tambah akun baru.
     */
    public function create()
    {
        $roles = ['admin', 'gudang', 'ceo'];
        return view('akunrole.create', compact('roles'));
    }

    /**
     * Menyimpan akun baru dengan proteksi jumlah CEO.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:admin,gudang,ceo'],
        ]);

        // FITUR KEAMANAN: Cek jika role CEO sudah ada
        if ($request->role === 'ceo') {
            $existingCeo = User::where('role', 'ceo')->exists();
            if ($existingCeo) {
                return redirect()->back()
                    ->with('error', 'Gagal: Akun CEO sudah ada. Sistem hanya mengizinkan satu akun dengan role CEO.')
                    ->withInput();
            }
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('akunrole.index')->with('success', 'Akun berhasil dibuat.');
    }

    /**
     * Form edit akun.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = ['admin', 'gudang', 'ceo'];
        return view('akunrole.edit', compact('user', 'roles'));
    }

    /**
     * Memperbarui data akun dengan pengecekan perpindahan role ke CEO.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'string', 'in:admin,gudang,ceo'],
        ]);

        // FITUR KEAMANAN: Mencegah perubahan role ke CEO jika CEO sudah ada (kecuali dirinya sendiri)
        if ($request->role === 'ceo' && $user->role !== 'ceo') {
            $existingCeo = User::where('role', 'ceo')->exists();
            if ($existingCeo) {
                return redirect()->back()
                    ->with('error', 'Gagal: Tidak bisa mengubah akun menjadi CEO karena akun CEO sudah ada.')
                    ->withInput();
            }
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('akunrole.index')->with('success', 'Data akun berhasil diperbarui.');
    }

    /**
     * Menghapus akun.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Proteksi: Tidak bisa menghapus diri sendiri
        if (auth()->id() == $user->id) {
            return redirect()->route('akunrole.index')->with('error', 'Peringatan: Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('akunrole.index')->with('success', 'Akun berhasil dihapus.');
    }
}