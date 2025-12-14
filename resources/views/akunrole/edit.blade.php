@extends('layouts.app') 

@section('title', 'Edit Akun Pengguna')

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
                    <i class="fas fa-user-edit"></i>
                </span>
                Edit Akun: {{ $user->name }}
            </h2>
            <p class="text-gray-500 text-sm mt-1 ml-1">Perbarui informasi profil atau ganti password pengguna.</p>
        </div>
        
        <a href="{{ route('akunrole.index') }}" 
           class="bg-white text-gray-600 border border-gray-300 hover:bg-gray-50 hover:text-gray-800 font-medium py-2 px-4 rounded-lg shadow-sm flex items-center transition-all duration-200">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    {{-- FORM UTAMA --}}
    <form method="POST" action="{{ route('akunrole.update', $user->id) }}" 
          class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
        @csrf
        @method('PUT')

        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            
            {{-- BAGIAN 1: INFORMASI PROFIL --}}
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center">
                    <i class="fas fa-id-card mr-2 text-indigo-500"></i> Informasi Profil
                </h3>

                {{-- Input Nama --}}
                <div class="mb-5 group">
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1 group-hover:text-indigo-600 transition-colors">Nama Lengkap</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-user"></i>
                        </span>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required 
                               class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm">
                    </div>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input Email (Disabled) --}}
                <div class="mb-5 group">
                    <label for="email" class="block text-sm font-semibold text-gray-500 mb-1">Email (Username)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input id="email" type="email" value="{{ $user->email }}" disabled 
                               class="w-full pl-10 border border-gray-200 rounded-lg p-2.5 bg-gray-100 text-gray-500 cursor-not-allowed shadow-inner">
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-lock text-xs"></i>
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 italic">Email tidak dapat diubah demi keamanan.</p>
                </div>
            </div>

            {{-- BAGIAN 2: KEAMANAN (PASSWORD) --}}
            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
                    <i class="fas fa-key mr-2 text-indigo-500"></i> Ganti Password
                </h3>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4 rounded-r shadow-sm">
                    <p class="text-xs text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i> 
                        Biarkan kosong jika tidak ingin mengganti password.
                    </p>
                </div>

                {{-- Password Baru --}}
                <div class="mb-4 relative">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                    <div class="relative">
                        <input id="password" type="password" name="password" 
                               class="w-full border border-gray-300 rounded-lg p-2.5 pr-10 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm">
                        <button type="button" data-target="password" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-indigo-600 focus:outline-none toggle-password transition-colors">
                            <i class="far fa-eye text-lg"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div class="mb-2 relative">
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password</label>
                    <div class="relative">
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               class="w-full border border-gray-300 rounded-lg p-2.5 pr-10 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm">
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
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
        </div>

    </form>
</div>

{{-- SCRIPT TOGGLE PASSWORD --}}
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