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
           class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg font-bold shadow-md hover:bg-indigo-700 active:scale-95 transform transition-all duration-200 flex items-center">
            <i class="fas fa-user-plus mr-2"></i> Tambah Akun
        </a>
    </div>

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex items-center">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- TABEL --}}
    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Pengguna</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dibuat Pada</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($users as $user)
                    <tr class="hover:bg-indigo-50/30 transition-colors">
                        <td class="px-6 py-4 bg-white">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-800">{{ $user->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 bg-white text-sm text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-4 bg-white text-sm">
                            @php
                                $roleColor = match(strtolower($user->role)) {
                                    'admin' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'gudang' => 'bg-green-100 text-green-800 border-green-200',
                                    'ceo' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    default => 'bg-gray-100 text-gray-800 border-gray-200',
                                };
                            @endphp
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full border {{ $roleColor }}">
                                {{ strtoupper($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 bg-white text-sm text-gray-500">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 bg-white text-sm text-center">
                            <div class="flex justify-center space-x-3">
                                <a href="{{ route('akunrole.edit', $user->id) }}" 
                                   class="text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-full transition-all duration-200 transform hover:scale-110 shadow-sm"
                                   title="Edit Akun">
                                    <i class="fas fa-user-edit"></i>
                                </a>

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