@extends('layouts.app') 

@section('title', 'Kelola Akun Pengguna')

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

<div class="max-w-7xl mx-auto py-6 animate-fade-in-up">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg mr-3 shadow-sm">
                    <i class="fas fa-users-cog"></i>
                </span>
                Pengelolaan Akun
            </h2>
            <p class="text-gray-500 text-sm mt-1 ml-1">Manajemen akses dan daftar pengguna sistem.</p>
        </div>
        
        <a href="{{ route('akunrole.create') }}" 
           class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg font-bold shadow-md hover:bg-indigo-700 hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center">
            <i class="fas fa-user-plus mr-2"></i> Tambah Akun
        </a>
    </div>

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-300" 
             x-transition:leave-start="opacity-100 transform translate-y-0" 
             x-transition:leave-end="opacity-0 transform -translate-y-2"
             class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded shadow-md relative mb-6 flex items-center">
            <i class="fas fa-check-circle mr-3 text-lg"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    {{-- TABEL DATA --}}
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16 text-center">
                            No
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Nama User
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Role
                        </th>
                        <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($users as $user)
                    <tr class="hover:bg-indigo-50 transition-colors duration-150 group">
                        
                        {{-- Nomor --}}
                        <td class="px-5 py-4 text-center text-sm text-gray-500 bg-white group-hover:bg-indigo-50">
                            {{ $loop->iteration }}
                        </td>

                        {{-- Nama User dengan Avatar --}}
                        <td class="px-5 py-4 bg-white group-hover:bg-indigo-50">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 bg-indigo-200 text-indigo-700 rounded-full flex items-center justify-center font-bold text-lg shadow-sm">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-gray-900 whitespace-no-wrap font-semibold">
                                        {{ $user->name }}
                                    </p>
                                    <p class="text-gray-400 text-xs">
                                        ID: #{{ $user->id }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Email --}}
                        <td class="px-5 py-4 bg-white group-hover:bg-indigo-50 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="far fa-envelope mr-2 text-gray-400"></i>
                                {{ $user->email }}
                            </div>
                        </td>

                        {{-- Role Badge --}}
                        <td class="px-5 py-4 bg-white group-hover:bg-indigo-50 text-center">
                            @php
                                $roleColor = match(strtolower($user->role)) {
                                    'admin' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'gudang' => 'bg-green-100 text-green-800 border-green-200',
                                    default => 'bg-gray-100 text-gray-800 border-gray-200',
                                };
                            @endphp
                            <span class="relative inline-block px-3 py-1 font-bold text-xs leading-tight uppercase tracking-wide rounded-full border {{ $roleColor }}">
                                {{ $user->role }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4 bg-white group-hover:bg-indigo-50 text-center text-sm font-medium">
                            <div class="flex justify-center space-x-3">
                                {{-- Tombol Edit --}}
                                <a href="{{ route('akunrole.edit', $user->id) }}" 
                                   class="text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-full transition-all duration-200 transform hover:scale-110 shadow-sm"
                                   title="Edit Akun">
                                    <i class="fas fa-user-edit"></i>
                                </a>

                                {{-- Tombol Hapus --}}
                                <form action="{{ route('akunrole.destroy', $user->id) }}" method="POST" 
                                      onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus akun {{ $user->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-full transition-all duration-200 transform hover:scale-110 shadow-sm"
                                            title="Hapus Akun">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection