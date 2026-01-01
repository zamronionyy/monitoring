@extends('layouts.app')

@section('title', 'Dashboard ' . ucfirst(auth()->user()->role))

@section('content')

{{-- STYLE --}}
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translate3d(0, 20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
    .animate-fade-in-up { animation-name: fadeInUp; animation-duration: 0.5s; animation-fill-mode: forwards; }
    
    /* Efek Kartu */
    .stat-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
    
    /* Icon Background */
    .icon-bg { transition: all 0.3s ease; }
    .stat-card:hover .icon-bg { transform: scale(1.1); }
</style>

<div class="max-w-7xl mx-auto py-6 animate-fade-in-up">

    {{-- HEADER (JUDUL + TANGGAL + FILTER) --}}
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
        {{-- Kiri: Judul --}}
        <div class="w-full md:w-auto">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg mr-3 shadow-sm">
                    <i class="fas fa-tachometer-alt"></i>
                </span>
                Dashboard {{ ucfirst(auth()->user()->role) }}
            </h2>
            <p class="text-gray-500 text-sm mt-1 ml-1">Ringkasan performa bisnis dan aktivitas terbaru.</p>
        </div>
        
        {{-- Kanan: Tanggal & Filter --}}
        <div class="flex flex-col items-end gap-3 w-full md:w-auto">
            <div class="text-sm text-gray-500 bg-white px-4 py-2 rounded-full shadow-sm border border-gray-100 flex items-center">
                <i class="far fa-calendar-alt mr-2 text-indigo-500"></i>
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </div>

            <div class="bg-white p-1 rounded-lg shadow-sm border border-gray-200 inline-flex">
                @foreach(['harian' => 'Hari Ini', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan', 'tahunan' => 'Tahunan'] as $key => $label)
                    <a href="{{ route('dashboard', ['filter' => $key]) }}" 
                       class="px-3 py-1.5 text-xs font-bold uppercase rounded transition-all {{ $activeFilter == $key ? 'bg-indigo-600 text-white shadow' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                       {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ================= KONTEN ADMIN & CEO (DIPERBAIKI) ================= --}}
    @if(in_array(auth()->user()->role, ['admin', 'ceo']))

        {{-- BARIS 1: KPI UTAMA --}}
        <h3 class="text-gray-700 font-semibold mb-3 flex items-center text-sm uppercase tracking-wide">
            <i class="fas fa-chart-line mr-2"></i> Performa Bisnis
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-xl shadow-lg p-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2"><i class="fas fa-boxes text-7xl"></i></div>
                <p class="text-indigo-100 font-medium mb-1 text-xs uppercase tracking-wider">Total SKU</p>
                <p class="text-3xl font-bold">{{ number_format($totalJenisBarang) }}</p>
                <div class="mt-2 text-xs text-indigo-200">Jenis Barang</div>
            </div>
            <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2"><i class="fas fa-shopping-cart text-7xl"></i></div>
                <p class="text-green-100 font-medium mb-1 text-xs uppercase tracking-wider">Total Transaksi</p>
                <p class="text-3xl font-bold">{{ number_format($totalPenjualan) }}</p>
                <div class="mt-2 text-xs text-green-200">Nota Terbit</div>
            </div>
            <div class="stat-card bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl shadow-lg p-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2"><i class="fas fa-users text-7xl"></i></div>
                <p class="text-emerald-100 font-medium mb-1 text-xs uppercase tracking-wider">Pelanggan</p>
                <p class="text-3xl font-bold">{{ number_format($totalCustomer) }}</p>
                <div class="mt-2 text-xs text-emerald-200">Terdaftar</div>
            </div>
            <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2"><i class="fas fa-wallet text-7xl"></i></div>
                <p class="text-purple-100 font-medium mb-1 text-xs uppercase tracking-wider">Total Omset</p>
                <p class="text-2xl font-bold">Rp {{ number_format($totalOmset, 0, ',', '.') }}</p>
                <div class="mt-2 text-xs text-purple-200">Pendapatan Kotor</div>
            </div>
        </div>

        {{-- BARIS 2: STATUS INVENTORI --}}
        <h3 class="text-gray-700 font-semibold mb-3 flex items-center text-sm uppercase tracking-wide">
            <i class="fas fa-warehouse mr-2"></i> Status Inventori
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card bg-white rounded-xl shadow border-b-4 border-cyan-500 p-5 flex justify-between items-center">
                <div><p class="text-gray-500 text-xs font-bold uppercase mb-1">Riwayat Masuk</p><h4 class="text-2xl font-bold text-gray-800">{{ number_format($sumTotalStokMasuk) }}</h4><span class="text-xs text-cyan-600">Total unit diterima</span></div>
                <div class="icon-bg w-12 h-12 rounded-full bg-cyan-50 flex items-center justify-center text-cyan-600"><i class="fas fa-arrow-down text-lg"></i></div>
            </div>
            <div class="stat-card bg-white rounded-xl shadow border-b-4 border-orange-500 p-5 flex justify-between items-center">
                <div><p class="text-gray-500 text-xs font-bold uppercase mb-1">Riwayat Keluar</p><h4 class="text-2xl font-bold text-gray-800">{{ number_format($sumTotalBarangKeluar) }}</h4><span class="text-xs text-orange-600">Total unit terjual</span></div>
                <div class="icon-bg w-12 h-12 rounded-full bg-orange-50 flex items-center justify-center text-orange-600"><i class="fas fa-arrow-up text-lg"></i></div>
            </div>
            <div class="stat-card bg-white rounded-xl shadow border-b-4 border-blue-500 p-5 flex justify-between items-center">
                <div><p class="text-gray-500 text-xs font-bold uppercase mb-1">Stok Fisik</p><h4 class="text-2xl font-bold text-gray-800">{{ number_format($totalStokAkhir) }}</h4><span class="text-xs text-blue-600">Tersedia di Gudang</span></div>
                <div class="icon-bg w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-layer-group text-lg"></i></div>
            </div>
            <div class="stat-card clickable-card bg-white rounded-xl shadow border-b-4 border-red-500 p-5 flex justify-between items-center relative overflow-hidden group" onclick="toggleModal()">
                <div class="relative z-10">
                    <p class="text-gray-500 text-xs font-bold uppercase mb-1 group-hover:text-red-600 transition-colors">Stok Kritis</p>
                    <h4 class="text-2xl font-bold text-red-600">{{ number_format($stokKritis) }}</h4>
                    <span class="text-xs text-red-500 font-medium group-hover:underline">→ Lihat Detail</span>
                </div>
                <div class="icon-bg w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-600 animate-pulse">
                    <i class="fas fa-exclamation-triangle text-lg"></i>
                </div>
                <div class="absolute inset-0 bg-red-50 opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>
            </div>
        </div>

        {{-- GRID UTAMA --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch"> 
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 flex flex-col h-[450px]"> 
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-800 flex items-center">
                        <span class="w-8 h-8 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center mr-2 text-sm"><i class="fas fa-chart-bar"></i></span>
                        Grafik Penjualan
                    </h3>
                </div>
                <div class="flex-grow relative w-full h-full"><canvas id="grafikPenjualan"></canvas></div>
            </div>
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 h-[450px] flex flex-col overflow-hidden"> 
                <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center"><h3 class="font-bold text-gray-800 flex items-center"><span class="w-8 h-8 rounded bg-green-100 text-green-600 flex items-center justify-center mr-2 text-sm"><i class="fas fa-history"></i></span>Transaksi Terkini</h3><a href="{{ route('barangkeluar.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wide">Lihat Semua</a></div>
                <div class="overflow-y-auto flex-grow">
                    <table class="min-w-full leading-normal text-sm">
                        <thead class="sticky top-0 bg-white shadow-sm z-10"><tr><th class="px-5 py-3 border-b bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase">Barang</th><th class="px-5 py-3 border-b bg-gray-50 text-center text-xs font-bold text-gray-500 uppercase">Qty</th><th class="px-5 py-3 border-b bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase">Pelanggan</th><th class="px-5 py-3 border-b bg-gray-50 text-right text-xs font-bold text-gray-500 uppercase">Tgl</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($recentSales as $sale)
                            <tr class="hover:bg-indigo-50 transition-colors duration-150"><td class="px-5 py-3.5 font-medium text-gray-700"><div class="truncate max-w-[140px]" title="{{ $sale['nama_barang'] }}">{{ $sale['nama_barang'] }}</div></td><td class="px-5 py-3.5 text-center"><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-bold">{{ $sale['jumlah'] }}</span></td><td class="px-5 py-3.5 text-gray-600"><div class="flex items-center"><div class="w-6 h-6 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-[10px] mr-2 font-bold">{{ substr($sale['pelanggan'], 0, 1) }}</div><span class="truncate max-w-[90px]">{{ $sale['pelanggan'] }}</span></div></td><td class="px-5 py-3.5 text-right text-xs text-gray-500">{{ substr($sale['tanggal'], 0, 6) }}</td></tr>
                            @empty <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500 italic">Belum ada data.</td></tr> @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    {{-- ================= KONTEN GUDANG ================= --}}
    @elseif(auth()->user()->role == 'gudang')
    
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6 relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2"><i class="fas fa-warehouse text-8xl"></i></div>
                <p class="text-blue-100 font-medium mb-1 text-sm uppercase tracking-wider">Stok Gudang</p>
                <h2 class="text-3xl font-bold">{{ number_format($totalStokAkhir) }}</h2>
                <div class="mt-4 text-xs text-blue-200"><i class="fas fa-check-circle mr-1"></i> Unit Tersedia</div>
            </div>
            <div class="stat-card bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-xl shadow-lg p-6 relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2"><i class="fas fa-tags text-8xl"></i></div>
                <p class="text-indigo-100 font-medium mb-1 text-sm uppercase tracking-wider">Total SKU</p>
                <p class="text-3xl font-bold">{{ number_format($totalJenisBarang) }}</p>
                <div class="mt-4 text-xs text-indigo-200"><i class="fas fa-list mr-1"></i> SKU Terdaftar</div>
            </div>
            <div class="stat-card bg-white border border-gray-200 rounded-xl shadow p-6 flex flex-col justify-between">
                <div><div class="flex justify-between items-start mb-2"><p class="text-gray-500 font-bold text-xs uppercase tracking-wider">Masuk Hari Ini</p><div class="bg-green-100 text-green-600 p-1.5 rounded"><i class="fas fa-arrow-down"></i></div></div><p class="text-3xl font-bold text-gray-800">{{ number_format($stokMasukHariIni) }}</p></div>
            </div>
            <div class="stat-card bg-white border border-gray-200 rounded-xl shadow p-6 flex flex-col justify-between">
                <div><div class="flex justify-between items-start mb-2"><p class="text-gray-500 font-bold text-xs uppercase tracking-wider">Keluar Hari Ini</p><div class="bg-yellow-100 text-yellow-600 p-1.5 rounded"><i class="fas fa-arrow-up"></i></div></div><p class="text-3xl font-bold text-gray-800">{{ number_format($stokKeluarHariIni) }}</p></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="stat-card clickable-card bg-red-50 border border-red-200 rounded-xl shadow p-6 flex flex-col justify-center items-center text-center group" onclick="toggleModal()">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4 text-2xl animate-pulse"><i class="fas fa-exclamation-triangle"></i></div>
                <h3 class="text-lg font-bold text-red-700">Peringatan Stok!</h3>
                <p class="text-sm text-red-600 mb-4">Terdapat barang dengan stok menipis.</p>
                <div class="text-4xl font-bold text-gray-800 mb-2">{{ number_format($stokKritis) }}</div>
                <span class="bg-red-200 text-red-800 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide group-hover:bg-red-300 transition-colors">Lihat Detail</span>
            </div>

            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center"><i class="fas fa-exchange-alt mr-2 text-blue-500"></i> Aktivitas Gudang</h3>
                </div>
                <div class="relative w-full h-[250px]"><canvas id="grafikGudang"></canvas></div>
            </div>
        </div>

    @endif
</div>

{{-- MODAL STOK KRITIS --}}
<div id="criticalModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="toggleModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex flex-col items-center">
                    <div class="flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4"><i class="fas fa-exclamation-triangle text-red-600 text-xl"></i></div>
                    <h3 class="text-lg leading-6 font-bold text-gray-900 text-center mb-2" id="modal-title">Stok Menipis (Kritis)</h3>
                    <p class="text-sm text-gray-500 text-center mb-6">Barang berikut memiliki stok di bawah batas minimum (5 unit). <br>Segera lakukan <i>restock</i>!</p>
                    <div class="w-full overflow-y-auto max-h-60 border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0"><tr><th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Nama Barang</th><th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Sisa Stok</th></tr></thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($criticalItemsList as $item)
                                    <tr class="hover:bg-red-50 transition-colors">
                                        <td class="px-4 py-3 text-sm text-gray-700 font-medium text-center"><div class="truncate max-w-[200px] mx-auto">{{ $item->nama_barang }}</div><div class="text-xs text-gray-400 font-mono mt-0.5">{{ $item->kode_barang }}</div></td>
                                        <td class="px-4 py-3 text-sm text-center font-bold {{ $item->stok_akhir <= 0 ? 'text-red-600' : 'text-yellow-600' }}">{{ $item->stok_akhir }}@if($item->stok_akhir <= 0) <span class="inline-block text-[10px] bg-red-100 text-red-800 px-1.5 py-0.5 rounded ml-1 border border-red-200 align-middle">HABIS</span> @endif</td>
                                    </tr>
                                @empty <tr><td colspan="2" class="px-4 py-6 text-center text-sm text-gray-500 italic">Aman! Tidak ada stok kritis saat ini.</td></tr> @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-center"><button type="button" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm transition-colors" onclick="toggleModal()">Tutup</button></div>
        </div>
    </div>
</div>

{{-- SCRIPT CHART JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function toggleModal() { const modal = document.getElementById('criticalModal'); modal.classList.toggle('hidden'); }
    document.addEventListener('DOMContentLoaded', function () {
        const userRole = "{{ auth()->user()->role }}";
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#6b7280';
        
        // DIPERBAIKI: Menambahkan role ceo pada pengecekan grafik
        if (userRole === 'admin' || userRole === 'ceo') {
            const ctx = document.getElementById('grafikPenjualan').getContext('2d');
            new Chart(ctx, { type: 'bar', data: { labels: {!! json_encode($grafikLabel ?? []) !!}, datasets: [{ label: 'Transaksi', data: {!! json_encode($grafikData ?? []) !!}, backgroundColor: '#4f46e5', borderRadius: 4, barPercentage: 0.5 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { borderDash: [2, 2], color: '#f3f4f6' }, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } } });
        } else if (userRole === 'gudang') {
            const ctxGudang = document.getElementById('grafikGudang').getContext('2d');
            new Chart(ctxGudang, { type: 'bar', data: { labels: {!! json_encode($grafikLabel ?? []) !!}, datasets: [{ label: 'Masuk', data: {!! json_encode($gudangMasukData ?? []) !!}, backgroundColor: '#22c55e', borderRadius: 4 }, { label: 'Keluar', data: {!! json_encode($gudangKeluarData ?? []) !!}, backgroundColor: '#f59e0b', borderRadius: 4 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', align: 'end', labels: { boxWidth: 10 } } }, scales: { y: { beginAtZero: true, grid: { borderDash: [2, 2], color: '#f3f4f6' }, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } } });
        }
    });
</script>
@endsection