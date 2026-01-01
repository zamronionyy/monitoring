@extends('layouts.app') 

@section('title', 'Tambah Akun Baru')

@section('content')

<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translate3d(0, 20px, 0); }
        to { opacity: 1; transform: translate3d(0, 0, 0); }
    }
    .animate-fade-in-up {
        animation-name: fadeInUp;
        animation-duration: 0.5s;
        animation-fill-mode: forwards;
    }
</style>

<div class="max-w-4xl mx-auto py-6 animate-fade-in-up">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg mr-3 shadow-sm">
                    <i class="fas fa-user-plus"></i>
                </span>
                Tambah Akun Baru
            </h2>
            <p class="text-gray-500 text-sm mt-1 ml-1">Buat akun baru untuk akses sistem (Admin/Gudang).</p>
        </div>
        
        <a href="{{ route('akunrole.index') }}" class="bg-white text-gray-600 border border-gray-300 hover:bg-gray-50 py-2 px-4 rounded-lg font-medium shadow-sm transition-all flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    {{-- Tampilkan Error Validasi --}}
    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-circle mr-2 text-lg"></i>
                <strong class="font-bold">Gagal Menyimpan!</strong>
            </div>
            <ul class="list-disc list-inside text-sm ml-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tambahkan autocomplete="off" di form utama --}}
    <form action="{{ route('akunrole.store') }}" method="POST" autocomplete="off" class="bg-white shadow-xl rounded-xl p-8 border border-gray-100 relative overflow-hidden">
        @csrf
        
        {{-- Hiasan Background --}}
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-indigo-50 rounded-full opacity-50 blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-32 h-32 bg-blue-50 rounded-full opacity-50 blur-2xl"></div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
            
            {{-- Nama Lengkap --}}
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                           autocomplete="off"
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-300" 
                           placeholder="Contoh: Budi Santoso">
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Alamat Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-envelope"></i>
                    </div>
                    {{-- Tambahkan autocomplete="new-password" (trik browser) atau "off" --}}
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required 
                           autocomplete="off"
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-300"
                           placeholder="nama@email.com">
                </div>
            </div>

            {{-- Role --}}
            <div>
                <label for="role" class="block text-sm font-semibold text-gray-700 mb-1">Role / Hak Akses</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                    <select name="role" id="role" required 
                            class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer bg-white">
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>ADMIN</option>
                        <option value="gudang" {{ old('role') == 'gudang' ? 'selected' : '' }}>GUDANG</option>
                    </select>
                </div>
            </div>

            {{-- Password (DENGAN ICON MATA & ANTI AUTOFILL) --}}
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <div class="relative">
                    {{-- Tambahkan autocomplete="new-password" --}}
                    <input type="password" name="password" id="password" required 
                           autocomplete="new-password"
                           class="w-full border border-gray-300 rounded-lg p-2.5 pr-10 focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                           placeholder="********">
                    {{-- Tombol Mata --}}
                    <button type="button" onclick="togglePassword('password', 'icon-pass')" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i id="icon-pass" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            {{-- Konfirmasi Password (DENGAN ICON MATA & ANTI AUTOFILL) --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password</label>
                <div class="relative">
                    {{-- Tambahkan autocomplete="new-password" --}}
                    <input type="password" name="password_confirmation" id="password_confirmation" required 
                           autocomplete="new-password"
                           class="w-full border border-gray-300 rounded-lg p-2.5 pr-10 focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                           placeholder="********">
                    {{-- Tombol Mata --}}
                    <button type="button" onclick="togglePassword('password_confirmation', 'icon-confirm')" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i id="icon-confirm" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

        </div>

        <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100 relative z-10">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-lg font-bold shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5 flex items-center">
                <i class="fas fa-save mr-2"></i> Simpan Akun
            </button>
        </div>
    </form>
</div>

{{-- SCRIPT TOGGLE PASSWORD --}}
<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>

@endsection