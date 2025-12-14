@extends('layouts.app')

@section('title', 'Tambah Barang Baru (Master)')

@section('content')
<h2 class="text-2xl font-bold mb-6">Tambah Barang Baru (Master)</h2>

<form action="{{ route('barang.store') }}" method="POST" class="bg-white shadow rounded-lg p-6">
    @csrf
    
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
    <div class="grid grid-cols-2 gap-4">
        <div class="mb-4">
            <label for="kode_barang" class="block text-sm font-medium text-gray-700">Kode Barang</label>
            <input type="text" name="kode_barang" id="kode_barang" value="{{ old('kode_barang') }}" class="w-full border rounded p-2 mt-1" required autofocus>
        </div>

        <div class="mb-4">
            <label for="nama_barang" class="block text-sm font-medium text-gray-700">Nama Barang</label>
            <input type="text" name="nama_barang" id="nama_barang" value="{{ old('nama_barang') }}" class="w-full border rounded p-2 mt-1" required>
        </div>

        <div class="mb-4">
            <label for="id_kategori" class="block text-sm font-medium text-gray-700">Kategori</label>
            
            <div class="relative mt-1">
                <select name="id_kategori" id="id_kategori" class="w-full border rounded p-2 appearance-none pr-10" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" {{ old('id_kategori') == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama_kategori }}
                        </option>
                    @endforeach
                </select>
                
            </div>
             <p class="mt-1 text-xs text-gray-500">
                Kategori baru? Tambahkan dulu via menu 'Manajemen Kategori'.
            </p>
        </div>

        <div class="mb-4">
            <label for="harga" class="block text-sm font-medium text-gray-700">Harga Jual (Rp)</label>
            <input type="number" name="harga" id="harga" value="{{ old('harga') }}" class="w-full border rounded p-2 mt-1" required>
        </div>
    </div>
    
    <div class="mt-6">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan Barang</button>
        <a href="{{ route('barang.index') }}" class="ml-3 text-gray-600 hover:underline">Batal</a>
    </div>
</form>
@endsection