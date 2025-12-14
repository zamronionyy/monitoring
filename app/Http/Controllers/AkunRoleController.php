<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AkunRoleController extends Controller
{
    /**
     * Tampilkan daftar akun user (selain admin).
     */
    public function index()
    {
        // Mengambil semua user yang memiliki role 'gudang'. 
        $users = User::where('role', 'gudang')->get();
        return view('akunrole.index', compact('users'));
    }

    /**
     * Tampilkan form untuk membuat user baru.
     */
    public function create()
    {
        return view('akunrole.create');
    }

    /**
     * Simpan user baru ke database dengan role 'gudang'.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'gudang', // **KUNCI:** Secara default di-set sebagai akun gudang
        ]);

        return redirect()->route('akunrole.index')->with('success', 'Akun Gudang berhasil ditambahkan.');
    }
    
    /**
     * Tampilkan form edit untuk akun Gudang tertentu.
     */
    public function edit(User $akunrole)
    {
        // PENTING: Pastikan hanya admin yang mengedit akun gudang, dan bukan akun admin lain.
        if ($akunrole->role !== 'gudang') {
             return back()->with('error', 'Akses ditolak. Akun yang dipilih bukan akun Gudang.');
        }
        
        // Menggunakan nama variabel $user untuk konsistensi di view
        return view('akunrole.edit', ['user' => $akunrole]);
    }
    
    /**
     * Menyimpan perubahan pada akun Gudang.
     */
    public function update(Request $request, User $akunrole)
    {
        // PENTING: Lindungi akun admin dan lindungi field email/role
        if ($akunrole->role !== 'gudang') {
            return back()->with('error', 'Akses ditolak. Akun yang dipilih bukan akun Gudang.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            // Email tidak boleh diubah
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], 
        ];

        $request->validate($rules);
        
        $akunrole->name = $request->name;

        // Jika password diisi, update password
        if ($request->filled('password')) {
            $akunrole->password = Hash::make($request->password);
        }

        $akunrole->save();

        return redirect()->route('akunrole.index')->with('success', 'Akun ' . $akunrole->name . ' berhasil diperbarui.');
    }

    /**
     * Hapus user Gudang.
     */
    public function destroy(User $akunrole)
    {
        // PENTING: Pastikan hanya menghapus user dengan role 'gudang'
        if ($akunrole->role !== 'gudang') {
            return back()->with('error', 'Akses ditolak. Anda hanya dapat menghapus akun Gudang.');
        }

        // Tidak boleh menghapus diri sendiri (jika admin sedang login)
        if (auth()->user()->id === $akunrole->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $akunrole->delete();

        return redirect()->route('akunrole.index')->with('success', 'Akun ' . $akunrole->name . ' berhasil dihapus.');
    }
}