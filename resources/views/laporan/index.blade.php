@extends('layouts.app')

@section('title', 'Laporan & Analisis Bisnis')

@section('content')

{{-- CHART JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

{{-- STYLE TAMBAHAN --}}
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
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
</style>

<div class="max-w-7xl mx-auto py-6 animate-fade-in-up" 
     x-data="{ 
        showOmsetModal: false, 
        showTransaksiModal: false, 
        showUnitModal: false, 
        showKritisModal: false 
     }">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div class="flex items-center">
            <div class="bg-blue-100 text-blue-600 p-3 rounded-lg mr-4 shadow-sm">
                <i class="fas fa-chart-line text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Laporan Penjualan</h2>
                <p class="text-gray-500 text-sm">Analisis kinerja bisnis dan pergerakan stok Anda.</p>
            </div>
        </div>
        
        {{-- Periode Badge --}}
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-2 rounded-lg text-sm font-medium flex items-center shadow-sm">
            <i class="far fa-calendar-alt mr-2"></i>
            Periode: <strong class="ml-1">{{ $results['tanggal_laporan'] ?? '-' }}</strong>
        </div>
    </div>

    {{-- ALERT MESSAGES --}}
    @if (session('success'))
        <div class="mb-6 px-4 py-3 rounded-lg bg-green-50 border-l-4 border-green-500 text-green-700 shadow-sm flex items-center">
            <i class="fas fa-check-circle mr-3 text-lg"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('warning'))
        <div class="mb-6 px-4 py-3 rounded-lg bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 shadow-sm flex items-center">
            <i class="fas fa-exclamation-triangle mr-3 text-lg"></i> {{ session('warning') }}
        </div>
    @endif

    {{-- FILTER SECTION --}}
    <form action="{{ route('laporan.filter') }}" method="GET" 
          class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100 mb-8 transition-shadow hover:shadow-xl">
        <div class="p-5 border-b border-gray-100 bg-gray-50 flex items-center">
            <h3 class="font-bold text-gray-800 flex items-center">
                <i class="fas fa-filter mr-2 text-indigo-500"></i> Filter Laporan
            </h3>
        </div>
        
        <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            {{-- Pilih Periode --}}
            <div class="group">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Rentang Waktu</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-calendar-day"></i>
                    </span>
                    <select name="tipe_periode" onchange="toggleCustomDates(this.value)"
                            class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all shadow-sm cursor-pointer bg-white">
                        <option value="bulanan" {{ (request('tipe_periode', $tipe_periode ?? '') == 'bulanan') ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="mingguan" {{ (request('tipe_periode', $tipe_periode ?? '') == 'mingguan') ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="tahunan" {{ (request('tipe_periode', $tipe_periode ?? '') == 'tahunan') ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="custom" {{ (request('tipe_periode', $tipe_periode ?? '') == 'custom') ? 'selected' : '' }}>Custom Tanggal</option>
                    </select>
                </div>
            </div>

            {{-- Custom Tanggal Mulai --}}
            <div id="custom-date-start" style="{{ (request('tipe_periode', $tipe_periode ?? '') == 'custom') ? '' : 'display: none;' }}">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Dari Tanggal</label>
                <div class="relative">
                    <input type="date" name="tanggal_mulai"
                           class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm"
                           value="{{ old('tanggal_mulai', $start_date ?? request('tanggal_mulai', '')) }}"
                           max="{{ date('Y-m-d') }}"> {{-- Perbaikan: max date --}}
                </div>
            </div>

            {{-- Custom Tanggal Selesai --}}
            <div id="custom-date-end" style="{{ (request('tipe_periode', $tipe_periode ?? '') == 'custom') ? '' : 'display: none;' }}">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Sampai Tanggal</label>
                <div class="relative">
                    <input type="date" name="tanggal_selesai"
                           class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm"
                           value="{{ old('tanggal_selesai', $end_date ?? request('tanggal_selesai', '')) }}"
                           max="{{ date('Y-m-d') }}"> {{-- Perbaikan: max date --}}
                </div>
            </div>

            {{-- Tombol Filter --}}
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg active:scale-95 transform transition-all duration-200 flex-1 flex justify-center items-center">
                    <i class="fas fa-search mr-2"></i> Terapkan
                </button>
                <a href="{{ route('laporan.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2.5 px-4 rounded-lg shadow-sm hover:shadow active:scale-95 transform transition-all duration-200 flex justify-center items-center" title="Reset Filter">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </div>
    </form>

    {{-- STATISTIC CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        
        {{-- Card 1: Total Omset --}}
        <div class="stat-card bg-white p-5 rounded-xl shadow-lg border-l-4 border-blue-500 cursor-pointer transition-all duration-300"
             @click="showOmsetModal = true">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Omset</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">
                        Rp {{ number_format($results['penjualan']['total_omset'] ?? 0, 0, ',', '.') }}
                    </h3>
                </div>
                <div class="bg-blue-100 text-blue-600 p-2 rounded-lg">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-blue-600 mt-3 font-medium flex items-center">
                <i class="fas fa-info-circle mr-1"></i> Klik untuk detail omset
            </p>
        </div>

        {{-- Card 2: Total Transaksi --}}
        <div class="stat-card bg-white p-5 rounded-xl shadow-lg border-l-4 border-indigo-500 cursor-pointer transition-all duration-300"
             @click="showTransaksiModal = true">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Transaksi</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">
                        {{ number_format($results['penjualan']['total_transaksi'] ?? 0) }} <span class="text-sm text-gray-500 font-normal">Nota</span>
                    </h3>
                </div>
                <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">
                    <i class="fas fa-receipt text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-indigo-600 mt-3 font-medium flex items-center">
                <i class="fas fa-info-circle mr-1"></i> Klik untuk detail transaksi
            </p>
        </div>

        {{-- Card 3: Unit Terjual --}}
        <div class="stat-card bg-white p-5 rounded-xl shadow-lg border-l-4 border-green-500 cursor-pointer transition-all duration-300"
             @click="showUnitModal = true">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Unit Terjual</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">
                        {{ number_format($results['penjualan']['total_qty_terjual'] ?? 0) }} <span class="text-sm text-gray-500 font-normal">Pcs</span>
                    </h3>
                </div>
                <div class="bg-green-100 text-green-600 p-2 rounded-lg">
                    <i class="fas fa-box-open text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-green-600 mt-3 font-medium flex items-center">
                <i class="fas fa-info-circle mr-1"></i> Klik untuk detail barang
            </p>
        </div>

        {{-- Card 4: Stok Kritis --}}
        <div class="stat-card bg-white p-5 rounded-xl shadow-lg border-l-4 border-red-500 cursor-pointer transition-all duration-300"
             @click="showKritisModal = true">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Stok Kritis</p>
                    <h3 class="text-2xl font-bold text-red-600 mt-1">
                        {{ number_format($results['stok']['stok_kritis_count'] ?? 0) }} <span class="text-sm text-gray-500 font-normal">SKU</span>
                    </h3>
                </div>
                <div class="bg-red-100 text-red-600 p-2 rounded-lg">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-red-600 mt-3 font-medium flex items-center">
                <i class="fas fa-arrow-right mr-1"></i> Lihat barang yang mau habis
            </p>
        </div>
    </div>


    {{-- CONTENT GRID (2 Column Layout) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        
        {{-- KOLOM KIRI (Lebar 2/3): TOP 10 BARANG --}}
        <div class="lg:col-span-2 bg-white border border-gray-100 shadow-lg rounded-xl overflow-hidden flex flex-col h-full">
            <div class="p-5 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-800 flex items-center">
                    <i class="fas fa-medal mr-2 text-yellow-500"></i> Top 10 Barang Terlaris (Unit)
                </h3>
            </div>
            <div class="overflow-x-auto flex-grow">
                <table class="w-full text-sm leading-normal">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-5 py-3 text-left">Nama Barang</th>
                            <th class="px-5 py-3 text-right">Unit Terjual</th>
                            <th class="px-5 py-3 text-right">Nilai (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($results['penjualan']['top_terjual'] ?? [] as $index => $item)
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="px-5 py-3 font-medium text-gray-700 flex items-center gap-2">
                                    <span class="w-5 h-5 flex items-center justify-center rounded-full text-xs font-bold {{ $index < 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $index + 1 }}
                                    </span>
                                    {{ $item->nama_barang ?? ($item['nama_barang'] ?? '-') }}
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-blue-600">
                                    {{ number_format($item->total_qty ?? ($item['total_qty'] ?? 0)) }}
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600">
                                    Rp {{ number_format($item->total_nilai ?? ($item['total_nilai'] ?? 0), 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-gray-500 italic">Belum ada data penjualan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- KOLOM KANAN (Lebar 1/3): KATEGORI & PELANGGAN --}}
        <div class="space-y-6">
            
            {{-- TOP KATEGORI --}}
            <div class="bg-white border border-gray-100 shadow-lg rounded-xl overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-indigo-50">
                    <h3 class="font-bold text-indigo-800 flex items-center">
                        <i class="fas fa-tags mr-2"></i> Kategori Populer
                    </h3>
                </div>
                <div class="p-4">
                    <ul class="space-y-3">
                        @forelse (collect($results['rekomendasi']['kategori_terlaris'] ?? [])->take(5) as $item)
                            <li class="flex justify-between items-center text-sm border-b border-gray-50 pb-2 last:border-0 last:pb-0">
                                <span class="font-medium text-gray-700">{{ $item->nama_kategori ?? '-' }}</span>
                                <span class="font-bold text-indigo-600 text-xs bg-indigo-100 px-2 py-1 rounded">
                                    Rp {{ number_format($item->total_omset ?? 0, 0, ',', '.') }}
                                </span>
                            </li>
                        @empty
                            <li class="text-center text-gray-500 text-sm py-2">Data kategori kosong.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- TOP PELANGGAN --}}
            <div class="bg-white border border-gray-100 shadow-lg rounded-xl overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-purple-50">
                    <h3 class="font-bold text-purple-800 flex items-center">
                        <i class="fas fa-crown mr-2"></i> Top Pelanggan
                    </h3>
                </div>
                <div class="p-4">
                    <ul class="space-y-3">
                        @forelse (collect($results['penjualan']['top_pelanggan'] ?? [])->take(5) as $item)
                            <li class="flex justify-between items-center text-sm border-b border-gray-50 pb-2 last:border-0 last:pb-0">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-bold">
                                        {{ substr($item->pelanggan->nama_pelanggan ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="font-medium text-gray-700 truncate max-w-[120px]">
                                        {{ $item->pelanggan->nama_pelanggan ?? 'Umum' }}
                                    </span>
                                </div>
                                <span class="font-bold text-purple-600 text-xs">
                                    Rp {{ number_format($item->total_nilai_pembelian ?? 0, 0, ',', '.') }}
                                </span>
                            </li>
                        @empty
                            <li class="text-center text-gray-500 text-sm py-2">Belum ada pelanggan.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>


    {{-- ANALISIS PERGERAKAN STOK --}}
    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-exchange-alt mr-2 text-gray-500"></i> Analisis Pergerakan Stok
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
        {{-- Fast Moving --}}
        <div class="bg-white border-l-4 border-green-400 shadow-md rounded-xl p-5">
            <h4 class="font-bold text-green-700 mb-3 flex items-center">
                <i class="fas fa-rocket mr-2"></i> Fast Moving (Cepat Laku)
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-green-50 text-green-800">
                        <tr>
                            <th class="p-2 text-left rounded-l-lg">Nama Barang</th>
                            <th class="p-2 text-right rounded-r-lg">Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($results['stok']['fast_moving'] ?? [] as $item)
                            <tr>
                                <td class="p-2 font-medium text-gray-700">{{ $item['nama_barang'] ?? '-' }}</td>
                                <td class="p-2 text-right font-bold text-green-600">{{ number_format($item['stok_akhir'] ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="p-3 text-center text-gray-400 italic">Data tidak tersedia.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Slow Moving --}}
        <div class="bg-white border-l-4 border-yellow-400 shadow-md rounded-xl p-5">
            <h4 class="font-bold text-yellow-700 mb-3 flex items-center">
                <i class="fas fa-hourglass-half mr-2"></i> Slow Moving (Laku Lambat)
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-yellow-50 text-yellow-800">
                        <tr>
                            <th class="p-2 text-left rounded-l-lg">Nama Barang</th>
                            <th class="p-2 text-right rounded-r-lg">Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($results['stok']['slow_moving'] ?? [] as $item)
                            <tr>
                                <td class="p-2 font-medium text-gray-700">{{ $item['nama_barang'] ?? '-' }}</td>
                                <td class="p-2 text-right font-bold text-yellow-600">{{ number_format($item['stok_akhir'] ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="p-3 text-center text-gray-400 italic">Data tidak tersedia.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    {{-- ==================== MODALS ==================== --}}
    
    {{-- Modal Template (Reusable Style) --}}
    <template x-if="true">
        <div x-show="showOmsetModal || showTransaksiModal || showUnitModal || showKritisModal" 
             class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" 
                 @click="showOmsetModal = false; showTransaksiModal = false; showUnitModal = false; showKritisModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                
                {{-- Modal Content --}}
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
                     x-show="showOmsetModal || showTransaksiModal || showUnitModal || showKritisModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    {{-- 1. MODAL TOTAL OMSET --}}
                    <div x-show="showOmsetModal">
                        <div class="bg-blue-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-blue-100">
                            <h3 class="text-lg font-bold text-blue-800 flex items-center">
                                <i class="fas fa-coins mr-2"></i> Detail Total Omset
                            </h3>
                            <button @click="showOmsetModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <p class="text-sm text-gray-600 mb-4">
                                Total pendapatan kotor dari seluruh transaksi pada periode ini. Berikut adalah kontributor terbesar:
                            </p>
                            <div class="overflow-y-auto max-h-60 border rounded-lg">
                                <table class="min-w-full text-sm divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Barang</th>
                                            <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Omset</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach (collect($results['penjualan']['top_terjual'] ?? [])->sortByDesc('total_nilai')->take(10) as $item)
                                            <tr>
                                                <td class="px-4 py-2 text-gray-700">{{ $item->nama_barang ?? '-' }}</td>
                                                <td class="px-4 py-2 text-right font-bold text-blue-600">Rp {{ number_format($item->total_nilai ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- 2. MODAL TOTAL TRANSAKSI --}}
                    <div x-show="showTransaksiModal">
                        <div class="bg-indigo-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-indigo-100">
                            <h3 class="text-lg font-bold text-indigo-800 flex items-center">
                                <i class="fas fa-receipt mr-2"></i> Detail Transaksi
                            </h3>
                            <button @click="showTransaksiModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                             <p class="text-sm text-gray-600 mb-4">
                                Jumlah nota/faktur yang diterbitkan. Berikut adalah pelanggan paling aktif:
                            </p>
                            <div class="overflow-y-auto max-h-60 border rounded-lg">
                                <table class="min-w-full text-sm divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Pelanggan</th>
                                            <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Jumlah Nota</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach ($results['penjualan']['top_pelanggan'] ?? [] as $item)
                                            <tr>
                                                <td class="px-4 py-2 text-gray-700">{{ $item->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                                                <td class="px-4 py-2 text-right font-bold text-indigo-600">{{ $item->total_nota }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- 3. MODAL UNIT TERJUAL --}}
                    <div x-show="showUnitModal">
                        <div class="bg-green-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-green-100">
                            <h3 class="text-lg font-bold text-green-800 flex items-center">
                                <i class="fas fa-box-open mr-2"></i> Detail Unit Terjual
                            </h3>
                            <button @click="showUnitModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                             <p class="text-sm text-gray-600 mb-4">
                                Total kuantitas barang yang keluar dari gudang. Barang dengan volume penjualan tertinggi:
                            </p>
                            <div class="overflow-y-auto max-h-60 border rounded-lg">
                                <table class="min-w-full text-sm divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Barang</th>
                                            <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Qty Terjual</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach ($results['penjualan']['top_terjual'] ?? [] as $item)
                                            <tr>
                                                <td class="px-4 py-2 text-gray-700">{{ $item->nama_barang ?? '-' }}</td>
                                                <td class="px-4 py-2 text-right font-bold text-green-600">{{ number_format($item->total_qty ?? 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- 4. MODAL STOK KRITIS --}}
                    <div x-show="showKritisModal">
                        <div class="bg-red-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-red-100">
                            <h3 class="text-lg font-bold text-red-800 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i> Stok Menipis (Kritis)
                            </h3>
                            <button @click="showKritisModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <p class="text-sm text-gray-600 mb-4">
                                Barang berikut memiliki stok di bawah batas minimum (5 unit). Segera lakukan <i>restock</i>!
                            </p>
                            <div class="overflow-y-auto max-h-60 border border-red-200 rounded-lg">
                                <table class="min-w-full text-sm divide-y divide-gray-200">
                                    <thead class="bg-red-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-bold text-red-700 uppercase">Nama Barang</th>
                                            <th class="px-4 py-2 text-right text-xs font-bold text-red-700 uppercase">Sisa Stok</th>
                                        </tr>
                                    </thead>
                                  <tbody class="divide-y divide-gray-100 bg-white">
                                        @php
                                            // PERBAIKAN: Ambil dari ['stok']['data'] yang berisi semua barang
                                            // Filter dimana stok_akhir <= 5
                                            $criticalItems = collect($results['stok']['data'] ?? [])->filter(function ($item) {
                                                return ($item['stok_akhir'] ?? 100) <= 5;
                                            })->sortBy('stok_akhir'); // Urutkan dari yang paling sedikit
                                        @endphp

                                        @forelse ($criticalItems as $item)
                                            <tr>
                                                <td class="px-4 py-2 text-gray-700">{{ $item['nama_barang'] ?? '-' }}</td>
                                                <td class="px-4 py-2 text-right font-bold text-red-600">
                                                    {{ number_format($item['stok_akhir'] ?? 0) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="p-4 text-center text-gray-500 italic">
                                                    Aman! Tidak ada stok kritis.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                        <button type="button" 
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                @click="showOmsetModal = false; showTransaksiModal = false; showUnitModal = false; showKritisModal = false">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </template>

</div>

{{-- SCRIPT CUSTOM DATES --}}
<script>
    function toggleCustomDates(value) {
        const start = document.getElementById('custom-date-start');
        const end = document.getElementById('custom-date-end');
        if (value === 'custom') {
            start.style.display = 'block';
            end.style.display = 'block';
        } else {
            start.style.display = 'none';
            end.style.display = 'none';
        }
    }
    // Init state
    document.addEventListener('DOMContentLoaded', () => {
        const select = document.querySelector('select[name="tipe_periode"]');
        if(select) toggleCustomDates(select.value);
    });
</script>

@endsection