@extends('layouts.app')

@section('title', 'Edit Pelanggan')

@section('content')
<h2 class="text-2xl font-bold mb-6">Edit Pelanggan: {{ $pelanggan->nama_pelanggan }}</h2>

<form action="{{ route('pelanggan.update', $pelanggan->id) }}" method="POST" class="bg-white shadow rounded-lg p-6">
    @csrf
    @method('PUT')
    
    <!-- ============================================= -->
    <!-- === INI BLOK ERROR YANG MEMPERBAIKI MASALAH ANDA === -->
    <!-- ============================================= -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Oops! Ada yang salah:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <!-- ============================================= -->

    <div class="grid grid-cols-2 gap-4">
        <div class="mb-4">
            <label for="kode_pelanggan" class="block text-sm font-medium text-gray-700">Kode Pelanggan (Otomatis)</label>
            <input type="text" id="kode_pelanggan" value="{{ $pelanggan->kode_pelanggan }}" class="w-full border rounded p-2 mt-1 bg-gray-100" disabled readonly>
        </div>

        <div class="mb-4">
            <label for="nama_pelanggan" class="block text-sm font-medium text-gray-700">Nama Pelanggan</label>
            <input type="text" name="nama_pelanggan" id="nama_pelanggan" value="{{ old('nama_pelanggan', $pelanggan->nama_pelanggan) }}" class="w-full border rounded p-2 mt-1" required>
        </div>

        <div class="mb-4">
            <label for="no_telp" class="block text-sm font-medium text-gray-700">No. Telepon</label>
            <input type="text" name="no_telp" id="no_telp" value="{{ old('no_telp', $pelanggan->no_telp) }}" class="w-full border rounded p-2 mt-1">
        </div>

        <div class="mb-4 col-span-2">
            <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
            <textarea name="alamat" id="alamat" rows="3" class="w-full border rounded p-2 mt-1">{{ old('alamat', $pelanggan->alamat) }}</textarea>
        </div>
    </div>

    <div class="mt-6">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update</button>
        <a href="{{ route('pelanggan.index') }}" class="ml-3 text-gray-600 hover:underline">Batal</a>
    </div>
</form>
@endsection