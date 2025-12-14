@extends('layouts.app') 

@section('title', 'Tambah Akun Baru')

@section('content')

{{-- STYLE ANIMASI --}}
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

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg mr-3 shadow-sm">
                    <i class="fas fa-user-plus"></i>
                </span>
                Tambah Akun Baru
            </h2>
            <p class="text-gray-500 text-sm mt-1 ml-1">Buat akun baru untuk akses sistem (Gudang/Admin).</p>
        </div>
        
        <a href="{{ route('akunrole.index') }}" 
           class="bg-white text-gray-600 border border-gray-300 hover:bg-gray-50 hover:text-gray-800 font-medium py-2 px-4 rounded-lg shadow-sm flex items-center transition-all duration-200">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    {{-- FORM CARD --}}
    <form method="POST" action="{{ route('akunrole.store') }}" 
          class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100 hover:shadow-xl transition-shadow duration-300">
        @csrf

        <div class="p-8">
            
            {{-- Bagian: Data Diri --}}
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center">
                <i class="fas fa-address-card mr-2 text-indigo-500"></i> Data Pengguna
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Input Nama --}}
                <div class="group">
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1 group-hover:text-indigo-600 transition-colors">Nama Lengkap</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-user"></i>
                        </span>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Contoh: Budi Santoso"
                               class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm">
                    </div>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input Email --}}
                <div class="group">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1 group-hover:text-indigo-600 transition-colors">Email (Username)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="nama@perusahaan.com"
                               class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Bagian: Keamanan --}}
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center mt-6">
                <i class="fas fa-shield-alt mr-2 text-indigo-500"></i> Keamanan Akun
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Input Password --}}
                <div class="group relative">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1 group-hover:text-indigo-600 transition-colors">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input id="password" type="password" name="password" required 
                               class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 pr-10 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm">
                        <button type="button" data-target="password" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-indigo-600 focus:outline-none toggle-password transition-colors">
                            <i class="far fa-eye text-lg"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input Konfirmasi Password --}}
                <div class="group relative">
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1 group-hover:text-indigo-600 transition-colors">Konfirmasi Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input id="password_confirmation" type="password" name="password_confirmation" required 
                               class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 pr-10 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm">
                        <button type="button" data-target="password_confirmation" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-indigo-600 focus:outline-none toggle-password transition-colors">
                            <i class="far fa-eye text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('akunrole.index') }}" 
               class="px-5 py-2.5 rounded-lg text-gray-600 font-medium hover:bg-gray-200 hover:text-gray-900 transition-colors">
                Batal
            </a>
            <button type="submit" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-bold shadow-md hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center">
                <i class="fas fa-save mr-2"></i> Simpan Akun
            </button>
        </div>

    </form>
</div>

{{-- SCRIPT TOGGLE PASSWORD (Sama dengan Edit) --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleButtons = document.querySelectorAll('.toggle-password');

        toggleButtons.forEach(button => {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
</script>
@endpush

@endsection