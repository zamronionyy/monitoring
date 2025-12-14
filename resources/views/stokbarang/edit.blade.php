@extends('layouts.app')

@section('title', 'Edit Data Stok Barang')

@section('content')

{{-- STYLE ANIMASI & FLATPICKR --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translate3d(0, 20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
    .animate-fade-in-up { animation-name: fadeInUp; animation-duration: 0.5s; animation-fill-mode: forwards; }
    .flatpickr-input[readonly] { background-color: white !important; cursor: pointer; }
</style>

<div class="max-w-4xl mx-auto py-6 animate-fade-in-up">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <span class="bg-yellow-100 text-yellow-600 p-2 rounded-lg mr-3 shadow-sm"><i class="fas fa-edit"></i></span> Edit Data Stok
            </h2>
            <p class="text-gray-500 text-sm mt-1 ml-1">Perbarui jumlah stok masuk atau tanggal pencatatan.</p>
        </div>
        <a href="{{ route('stokbarang.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors flex items-center font-medium bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm hover:bg-gray-50"><i class="fas fa-arrow-left mr-2"></i> Kembali</a>
    </div>

    <form action="{{ route('stokbarang.update', $stok->id) }}" method="POST" class="bg-white shadow-lg rounded-xl p-8 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
        @csrf @method('PUT')

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-6 shadow-sm flex items-start">
                <i class="fas fa-exclamation-triangle mt-1 mr-2 shrink-0"></i>
                <div><strong class="font-bold">Periksa kembali inputan Anda:</strong><ul class="list-disc list-inside text-sm mt-1">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul></div>
            </div>
        @endif

        <div class="mb-6 pb-6 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center"><i class="fas fa-info-circle mr-2"></i> Informasi Barang (Tidak dapat diubah)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group"><label class="block text-sm font-medium text-gray-500 mb-1">Kode Barang</label><div class="relative"><span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-barcode"></i></span><input type="text" value="{{ $stok->barang->kode_barang ?? 'N/A' }}" class="w-full pl-10 border border-gray-200 rounded-lg p-2.5 bg-gray-100 text-gray-500 cursor-not-allowed font-mono" disabled></div></div>
                <div class="group"><label class="block text-sm font-medium text-gray-500 mb-1">Kategori</label><div class="relative"><span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-tag"></i></span><input type="text" value="{{ $stok->barang->kategori->nama_kategori ?? 'N/A' }}" class="w-full pl-10 border border-gray-200 rounded-lg p-2.5 bg-gray-100 text-gray-500 cursor-not-allowed" disabled></div></div>
                <div class="group md:col-span-2"><label class="block text-sm font-medium text-gray-500 mb-1">Nama Barang</label><div class="relative"><span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-box"></i></span><input type="text" value="{{ $stok->barang->nama_barang ?? 'N/A' }}" class="w-full pl-10 border border-gray-200 rounded-lg p-2.5 bg-gray-100 text-gray-500 cursor-not-allowed font-bold" disabled></div></div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 flex items-center"><i class="fas fa-pen mr-2"></i> Data Stok (Dapat diedit)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group">
                    <label for="stok" class="block text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors mb-1">Jumlah Stok Masuk</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-layer-group"></i></span>
                        <input type="number" name="stok" id="stok" value="{{ old('stok', $stok->stok) }}" class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm text-gray-800 font-semibold" required>
                    </div>
                </div>

                {{-- TANGGAL MASUK (FLATPICKR) --}}
                <div class="group">
                    <label for="tanggal_masuk" class="block text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors mb-1">Tanggal Masuk</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-calendar-alt"></i></span>
                        {{-- Gunakan Carbon untuk format awal Y-m-d agar terbaca Flatpickr --}}
                        <input type="text" name="tanggal_masuk" id="tanggal_masuk" 
                               value="{{ old('tanggal_masuk', \Carbon\Carbon::parse($stok->tanggal_masuk)->format('Y-m-d')) }}" 
                               class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm cursor-pointer bg-white" 
                               required>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100">
            <a href="{{ route('stokbarang.index') }}" class="px-5 py-2.5 rounded-lg text-gray-600 font-medium hover:bg-gray-100 hover:text-red-600 transition-colors">Batal</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-bold shadow-md hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center"><i class="fas fa-save mr-2"></i> Perbarui Data</button>
        </div>
    </form>
</div>

{{-- SCRIPT FLATPICKR --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr("#tanggal_masuk", {
            dateFormat: "Y-m-d", // Simpan ke DB tetap Y-m-d
            altInput: true,      // Tampilan user
            altFormat: "j F Y",  // Contoh: 8 Desember 2025
            locale: "id",        // Bahasa Indonesia
            maxDate: "today",    // Batasi sampai hari ini
            allowInput: true
        });
    });
</script>

@endsection