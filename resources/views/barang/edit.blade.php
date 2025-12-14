@extends('layouts.app')

@section('title', 'Edit Barang')

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

<div class="max-w-4xl mx-auto py-4 animate-fade-in-up">

    {{-- HEADER YANG DIPERBAIKI --}}
    <div class="mb-6">
        {{-- Tombol Kembali diletakkan di atas agar aman --}}
        <div class="mb-4">
            <a href="{{ route('barang.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors inline-flex items-center font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Barang
            </a>
        </div>
        
        {{-- Judul Utama dengan Penanganan Teks Panjang --}}
        <div class="flex items-start">
            <span class="bg-indigo-100 text-indigo-600 p-3 rounded-lg mr-4 shadow-sm shrink-0">
                <i class="fas fa-edit text-xl"></i>
            </span>
            <div>
                <h2 class="text-lg font-bold text-gray-800">Edit Barang</h2>
                {{-- Nama barang akan dipotong (...) jika terlalu panjang --}}
                <h3 class="text-xl sm:text-2xl font-bold text-indigo-700 mt-1 leading-tight line-clamp-2" title="{{ $barang->nama_barang }}">
                    {{ $barang->nama_barang }}
                </h3>
            </div>
        </div>
    </div>

    {{-- FORM CARD --}}
    <form action="{{ route('barang.update', $barang->id) }}" method="POST" 
          class="bg-white shadow-lg rounded-xl p-8 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
        @csrf
        @method('PUT') 
        
        {{-- ERROR HANDLING --}}
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-6 shadow-sm flex items-start">
                <i class="fas fa-exclamation-triangle mt-1 mr-2 shrink-0"></i>
                <div>
                    <strong>Periksa Inputan Anda:</strong>
                    <ul class="list-disc list-inside mt-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                {{ session('error') }}
            </div>
        @endif

        {{-- GRID INPUT --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- Kode Barang --}}
            <div class="group">
                <label for="kode_barang" class="block text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors mb-1">
                    Kode Barang
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-barcode"></i>
                    </span>
                    <input type="text" name="kode_barang" id="kode_barang" 
                           value="{{ old('kode_barang', $barang->kode_barang) }}" 
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm" 
                           required autofocus>
                </div>
            </div>

            {{-- Nama Barang (Textarea agar bisa multi-line saat edit) --}}
            <div class="group md:col-span-2">
                <label for="nama_barang" class="block text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors mb-1">
                    Nama Barang
                </label>
                <div class="relative">
                    <span class="absolute top-3 left-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-box"></i>
                    </span>
                    {{-- Gunakan textarea untuk nama yang panjang agar lebih mudah diedit --}}
                    <textarea name="nama_barang" id="nama_barang" rows="2"
                              class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm resize-y" 
                              required>{{ old('nama_barang', $barang->nama_barang) }}</textarea>
                </div>
            </div>

            {{-- Kategori --}}
            <div class="group">
                <label for="id_kategori" class="block text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors mb-1">
                    Kategori
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-tags"></i>
                    </span>
                    <select name="id_kategori" id="id_kategori" 
                            class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 appearance-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm bg-white cursor-pointer pr-10" 
                            required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ old('id_kategori', $barang->id_kategori) == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- Harga Jual --}}
            <div class="group">
                <label for="harga" class="block text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors mb-1">
                    Harga Jual (Rp)
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-bold">
                        Rp
                    </span>
                    <input type="number" name="harga" id="harga" 
                           value="{{ old('harga', $barang->harga) }}" 
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm" 
                           required>
                </div>
            </div>
        </div>

        {{-- BUTTON ACTIONS --}}
        <div class="mt-8 flex items-center space-x-4 border-t border-gray-100 pt-6">
            <button type="submit" 
                    class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-semibold shadow-md hover:bg-indigo-700 hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center">
                <i class="fas fa-save mr-2"></i> Update Barang
            </button>
            <a href="{{ route('barang.index') }}" 
               class="text-gray-600 hover:text-red-600 hover:underline transition-colors font-medium px-4">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection