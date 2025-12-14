@extends('layouts.app') 

@section('title', 'Manajemen Pelanggan')

@section('content')

<a href="{{ route('pelanggan.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mb-4 inline-block">
    Tambah Pelanggan
</a>

@if (session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
         x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
         x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Kode
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Nama Pelanggan
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Alamat
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    No. Telp
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Aksi
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pelanggans as $pelanggan)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm">
                        {{ $pelanggan->kode_pelanggan }}
                    </td>
                    <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm">
                        {{ $pelanggan->nama_pelanggan }}
                    </td>
                    <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm">
                        {{ $pelanggan->alamat ?? '-' }}
                    </td>
                    <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm">
                        {{ $pelanggan->no_telp ?? '-' }}
                    </td>
                    <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm">
                        <a href="{{ route('pelanggan.edit', $pelanggan->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        <form action="{{ route('pelanggan.destroy', $pelanggan->id) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Apakah Anda yakin?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-3 border-b border-gray-200 bg-white text-sm text-center">
                        Data pelanggan masih kosong.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="px-5 py-3 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
        {{ $pelanggans->links('pagination::tailwind') }}
    </div>
</div>
@endsection