@extends('layouts.app')

@section('title', 'Tambah Stok Barang')

@section('content')

{{-- STYLE TAMBAHAN (Select2 & Flatpickr) --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
{{-- CSS Flatpickr (Kalender Bagus) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translate3d(0, 20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
    .animate-fade-in-up { animation-name: fadeInUp; animation-duration: 0.5s; animation-fill-mode: forwards; }

    /* CUSTOM SELECT2 */
    .select2-container .select2-selection--single { height: 46px !important; border: 1px solid #d1d5db !important; border-radius: 0.5rem !important; display: flex !important; align-items: center !important; background-color: white !important; padding-left: 0.5rem !important; transition: all 0.2s; }
    .select2-container--default .select2-selection--single:focus, .select2-container--open .select2-selection--single { border-color: #6366f1 !important; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3) !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 46px !important; font-size: 0.95rem !important; color: #374151 !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px !important; right: 10px !important; }
    
    /* STYLE UNTUK INPUT TANGGAL YANG DIKUNCI */
    .bg-locked { background-color: #f3f4f6 !important; cursor: not-allowed; }
</style>

<div class="max-w-4xl mx-auto py-6 animate-fade-in-up">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <span class="bg-green-100 text-green-600 p-2 rounded-lg mr-3 shadow-sm"><i class="fas fa-cubes"></i></span> Tambah Stok Masuk
            </h2>
            <p class="text-gray-500 text-sm mt-1 ml-1">Catat transaksi barang masuk ke gudang.</p>
        </div>
        <a href="{{ route('stokbarang.index') }}" class="bg-white text-gray-600 border border-gray-300 hover:bg-gray-50 hover:text-gray-800 font-medium py-2 px-4 rounded-lg shadow-sm flex items-center transition-all duration-200"><i class="fas fa-arrow-left mr-2"></i> Kembali</a>
    </div>

    <form action="{{ route('stokbarang.store') }}" method="POST" class="bg-white shadow-lg rounded-xl p-8 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
        @csrf

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-6 shadow-sm flex items-start">
                <i class="fas fa-exclamation-triangle mt-1 mr-3 text-lg shrink-0"></i>
                <div>
                    <strong class="font-bold">Oops! Ada kesalahan input:</strong>
                    <ul class="list-disc list-inside text-sm mt-1">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- 1. PILIH BARANG --}}
            <div class="md:col-span-2">
                <label for="id_barang" class="block text-sm font-semibold text-gray-700 mb-1">Pilih Barang <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select name="id_barang" id="id_barang" class="w-full" required>
                        <option value="">-- Cari Nama atau Kode Barang --</option>
                        @foreach($barangs as $barang)
                            <option value="{{ $barang->id }}" {{ old('id_barang') == $barang->id ? 'selected' : '' }}>({{ $barang->kode_barang }}) - {{ $barang->nama_barang }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 2. JUMLAH STOK --}}
            <div class="group">
                <label for="stok" class="block text-sm font-semibold text-gray-700 mb-1 group-hover:text-indigo-600 transition-colors">Jumlah Masuk <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-box-open"></i></span>
                    <input type="number" name="stok" id="stok" value="{{ old('stok') }}" class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm font-semibold text-gray-800" required min="1" placeholder="0">
                </div>
            </div>

            {{-- 3. TANGGAL MASUK (DIKUNCI KE HARI INI - BERLAKU UNTUK SEMUA ROLE TERMASUK GUDANG) --}}
            <div class="group">
                <label for="tanggal_masuk" class="block text-sm font-semibold text-gray-700 mb-1 group-hover:text-indigo-600 transition-colors">Tanggal Masuk (Hari Ini) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-calendar-alt"></i></span>
                    {{-- Input dikunci dengan readonly dan class visual bg-locked --}}
                    <input type="text" name="tanggal_masuk" id="tanggal_masuk"
                           value="{{ date('Y-m-d') }}"
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 bg-locked cursor-not-allowed"
                           readonly required>
                </div>
                <p class="text-xs text-gray-400 mt-1 italic">* Tanggal otomatis diset secara real-time.</p>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100">
            <a href="{{ route('stokbarang.index') }}" class="px-5 py-2.5 rounded-lg text-gray-600 font-medium hover:bg-gray-100 hover:text-red-600 transition-colors">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-bold shadow-md hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center"><i class="fas fa-save mr-2"></i> Simpan Stok</button>
        </div>
    </form>
</div>

{{-- SCRIPTS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
{{-- JS Flatpickr + Bahasa Indonesia --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('#id_barang').select2({ placeholder: "-- Pilih Barang --", allowClear: true, width: '100%' });
        $(document).on('select2:open', () => { document.querySelector('.select2-search__field').focus(); });

        // Flatpickr (Dikonfigurasi agar tidak bisa dibuka/diedit oleh role gudang maupun admin)
        flatpickr("#tanggal_masuk", {
            dateFormat: "Y-m-d", 
            altInput: true,      
            altFormat: "j F Y",  
            locale: "id",        
            defaultDate: "today",
            clickOpens: false // Mencegah kalender terbuka saat diklik untuk menjaga real-time data
        });
    });
</script>

@endsection