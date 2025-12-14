@extends('layouts.app')

@section('title', 'Tambah Pelanggan Baru')

@section('content')

<h2 class="text-2xl font-bold mb-6">Tambah Pelanggan Baru</h2>

<form action="{{ route('pelanggan.store') }}" method="POST" class="bg-white shadow rounded-lg p-6">
@csrf

<!-- ============================================= -->
<!-- === INI ADALAH BLOK ERROR YANG SEHARUSNYA === -->
<!-- (Pastikan kode Anda persis seperti ini) -->
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

    <!-- Input Kode Pelanggan sudah dihapus (otomatis) -->

    <div class="mb-4">
        <label for="nama_pelanggan" class="block text-sm font-medium text-gray-700">Nama Pelanggan</label>
        <!-- value="{{ old('nama_pelanggan') }}" akan mengembalikan "14" yang salah -->
        <input type="text" name="nama_pelanggan" id="nama_pelanggan" value="{{ old('nama_pelanggan') }}" class="w-full border rounded p-2 mt-1" required autofocus>
    </div>

    <div class="mb-4">
        <label for="no_telp" class="block text-sm font-medium text-gray-700">No. Telepon</label>
        <!-- value="{{ old('no_telp') }}" akan mengembalikan "as" yang salah -->
        <input type="text" name="no_telp" id="no_telp" value="{{ old('no_telp') }}" class="w-full border rounded p-2 mt-1" placeholder="Contoh: 08123456789">
    </div>

    <div class="mb-4 col-span-2">
        <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
        <textarea name="alamat" id="alamat" rows="3" class="w-full border rounded p-2 mt-1">{{ old('alamat') }}</textarea>
    </div>
</div>

<div class="mt-6">
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
    <a href="{{ route('pelanggan.index') }}" class="ml-3 text-gray-600 hover:underline">Batal</a>
</div>

</form>
@endsection