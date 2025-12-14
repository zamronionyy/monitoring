@extends('layouts.app')

@section('title', 'Barang Keluar (Penjualan)')

@section('content')

{{-- STYLE FLATPICKR --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translate3d(0, 20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
    .animate-fade-in-up { animation-name: fadeInUp; }
    /* Fix input flatpickr background */
    .flatpickr-input[readonly] { background-color: white !important; cursor: pointer; }
</style>

<div class="opacity-0 animate-fade-in-up" style="animation: fadeInUp 0.5s ease-out forwards;">
    
    <div class="mb-6 space-y-4">
        {{-- TOMBOL AKSI --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex space-x-2">
                @if(auth()->user()->role == 'admin')
                    <a href="{{ route('barangkeluar.create') }}" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-blue-700 active:scale-95 transform transition-all duration-200 flex items-center">
                        <i class="fas fa-plus mr-2"></i> Tambah Penjualan
                    </a>
                    <a href="{{ route('pelanggan.index') }}" 
                       class="bg-yellow-500 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-yellow-600 active:scale-95 transform transition-all duration-200 flex items-center">
                        <i class="fas fa-users mr-2"></i> Manajemen Pelanggan
                    </a>
                @endif
            </div>
        </div>

        {{-- PANEL FILTER --}}
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <form id="filterForm" action="{{ route('barangkeluar.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end" novalidate>
                
                {{-- Input Tanggal Mulai (Flatpickr) --}}
                <div class="w-full lg:w-auto flex-1">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Dari Tanggal</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" id="start_date" name="start_date" value="{{ request('start_date') }}" 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer bg-white"
                               placeholder="Pilih Tanggal...">
                    </div>
                </div>

                {{-- Input Tanggal Selesai (Flatpickr) --}}
                <div class="w-full lg:w-auto flex-1">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Sampai Tanggal</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" id="end_date" name="end_date" value="{{ request('end_date') }}" 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer bg-white"
                               placeholder="Pilih Tanggal...">
                    </div>
                </div>

               {{-- Input Pencarian --}}
                <div class="w-full lg:w-[40%]">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Cari Transaksi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-search"></i></span>
                        
                        {{-- PERBAIKAN: Ditambahkan autocomplete="off" --}}
                        <input type="text" name="search" value="{{ request('search') }}" 
                               autocomplete="off"
                               placeholder="Cari ID Transaksi, Pelanggan..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                </div>

                {{-- Tombol Filter & Reset --}}
                <div class="flex gap-2 w-full lg:w-auto">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-bold shadow-md transition-all flex items-center justify-center h-[42px] flex-1 lg:flex-none">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    
                    @if(request('search') || request('start_date') || request('end_date'))
                        <a href="{{ route('barangkeluar.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg font-bold shadow-sm transition-all flex items-center justify-center h-[42px]" title="Reset Filter">
                            <i class="fas fa-undo"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ALERT --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded shadow-md relative mb-4 flex items-center"><i class="fas fa-check-circle mr-2"></i> <span>{{ session('success') }}</span></div>
    @endif
    @if (session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded shadow-md relative mb-4 flex items-center"><i class="fas fa-exclamation-circle mr-2"></i> <span>{{ session('error') }}</span></div>
    @endif

    {{-- TABEL DATA --}}
    <div class="bg-white shadow-lg rounded-lg overflow-hidden transition-shadow duration-300 hover:shadow-xl">
        @if ($barangKeluars->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-box-open mb-2 text-2xl block"></i>
                @if(request('search') || request('start_date')) Data tidak ditemukan untuk filter yang dipilih. @else Belum ada data barang keluar. @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">ID Transaksi</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nama Pelanggan</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Total Harga</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Dicatat Oleh</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($barangKeluars as $transaksi)
                            <tr class="hover:bg-blue-50 transition-colors duration-200 group">
                                <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm font-medium text-blue-600 whitespace-nowrap">
                                    <span class="bg-blue-100 text-blue-800 py-1 px-2 rounded text-xs font-bold">{{ $transaksi->id_transaksi }}</span>
                                </td>
                                <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm font-semibold text-gray-700">
                                    {{ $transaksi->pelanggan->nama_pelanggan ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm text-gray-600 whitespace-nowrap">
                                    <i class="far fa-calendar-alt mr-1 text-gray-400"></i>
                                    {{-- FORMAT TANGGAL INDONESIA --}}
                                    {{ \Carbon\Carbon::parse($transaksi->tanggal)->translatedFormat('d F Y') }}
                                </td>
                                <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm font-bold text-green-600 whitespace-nowrap">
                                    Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <div class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center text-xs mr-2">{{ substr($transaksi->user->name ?? 'U', 0, 1) }}</div>
                                        {{ $transaksi->user->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('barangkeluar.detail', $transaksi->id) }}" class="text-blue-500 hover:text-blue-700 transform hover:scale-110 transition duration-150" title="Lihat Detail"><i class="fas fa-eye text-lg"></i></a>
                                        @if(auth()->user()->role == 'admin')
                                            <a href="{{ route('barangkeluar.edit', $transaksi->id) }}" class="text-yellow-500 hover:text-yellow-700 transform hover:scale-110 transition duration-150" title="Edit Data"><i class="fas fa-edit text-lg"></i></a>
                                            <button type="button" onclick="konfirmasiHapus('{{ $transaksi->id }}', '{{ $transaksi->id_transaksi }}')" class="text-red-500 hover:text-red-700 transform hover:scale-110 transition duration-150" title="Hapus Data"><i class="fas fa-trash text-lg"></i></button>
                                            <form id="delete-form-{{ $transaksi->id }}" action="{{ route('barangkeluar.destroy', $transaksi->id) }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 bg-gray-50 border-t flex flex-col xs:flex-row items-center xs:justify-between">
                {{ $barangKeluars->links('pagination.tailwind-custom') }}
            </div>
        @endif
    </div>
</div>

{{-- Script SweetAlert2 & Flatpickr --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<script>
    // Init Flatpickr Filter
    document.addEventListener("DOMContentLoaded", function() {
        const config = {
            dateFormat: "Y-m-d", // Format ke Server
            altInput: true,      // Tampilan User
            altFormat: "j F Y",  // Format: 8 Desember 2025
            locale: "id",        // Bahasa Indonesia
            allowInput: true
        };
        flatpickr("#start_date", config);
        flatpickr("#end_date", config);
    });

    // Konfirmasi Hapus
    function konfirmasiHapus(id, noTransaksi) {
        Swal.fire({
            title: 'Apakah Anda Yakin?', text: "Data " + noTransaksi + " akan dihapus permanen!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#EF4444', cancelButtonColor: '#6B7280', confirmButtonText: 'Ya, Hapus!'
        }).then((result) => { if (result.isConfirmed) document.getElementById('delete-form-' + id).submit(); })
    }
    
    // Validasi Form Filter
    document.getElementById('filterForm').addEventListener('submit', function(event) {
        const s = this.querySelector('input[name="start_date"]');
        const e = this.querySelector('input[name="end_date"]');
        // Flatpickr membuat input hidden asli, kita cek validity-nya
        // Atau jika kosong salah satu tapi satunya isi
        if ((s.value && !e.value) || (!s.value && e.value)) {
            event.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Filter Tanggal Tidak Lengkap', text: 'Harap isi kedua tanggal (Dari & Sampai) atau kosongkan keduanya.', confirmButtonColor: '#4F46E5' });
        }
    });
</script>
@endsection