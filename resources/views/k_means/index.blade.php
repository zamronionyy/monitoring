@extends('layouts.app') 

@section('title', 'Analisis K-Means Clustering')

@section('content')

{{-- CHART JS & FLATPICKR --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

{{-- STYLE TAMBAHAN --}}
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translate3d(0, 20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
    .animate-fade-in-up { animation-name: fadeInUp; animation-duration: 0.5s; animation-fill-mode: forwards; }
    .chart-container { position: relative; height: 100%; width: 100%; min-height: 300px; }
    .flatpickr-input[readonly] { background-color: white !important; cursor: pointer; }
    
    /* Scrollbar Halus untuk List Produk Panjang */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>

<div class="max-w-7xl mx-auto py-6 animate-fade-in-up">

    {{-- HEADER --}}
   <div class="flex items-center gap-4 mb-8">
        <div class="bg-indigo-100 text-indigo-600 w-12 h-12 rounded-lg flex items-center justify-center shadow-sm shrink-0">
            <i class="fas fa-layer-group text-xl"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Analisis K-Means (Clustering)</h2>
            <p class="text-gray-500 text-sm">Pengelompokan data kategori barang berdasarkan pola penjualan.</p>
        </div>
    </div>

    {{-- ALERT ERROR/WARNING --}}
    @if (session('error'))
        <div class="mb-6 px-4 py-4 rounded-lg bg-red-50 border-l-4 border-red-500 text-red-800 shadow-sm flex items-start">
            <i class="fas fa-times-circle mt-1 mr-3 text-lg shrink-0"></i>
            <div><strong class="font-bold">Error:</strong><p class="text-sm mt-1">{{ session('error') }}</p></div>
        </div>
    @endif
    @if (session('warning'))
        <div class="mb-6 px-4 py-4 rounded-lg bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 shadow-sm flex items-start">
            <i class="fas fa-exclamation-triangle mt-1 mr-3 text-lg shrink-0"></i>
            <div><strong class="font-bold">Perhatian:</strong><p class="text-sm mt-1">{{ session('warning') }}</p></div>
        </div>
    @endif

    {{-- FORM PARAMETER --}}
    <form action="{{ route('k_means.run') }}" method="POST" 
          class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100 mb-10 hover:shadow-xl transition-shadow duration-300">
        @csrf
        
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h3 class="font-bold text-gray-800 flex items-center">
                <i class="fas fa-sliders-h mr-2 text-indigo-500"></i> Parameter Analisis
            </h3>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            
            {{-- Tanggal Mulai --}}
            <div class="group">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Dari Tanggal</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="tanggal_mulai" id="tanggal_mulai" required
                           value="{{ $inputs['tanggal_mulai'] ?? now()->startOfMonth()->format('Y-m-d') }}"
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none shadow-sm cursor-pointer bg-white"
                           placeholder="Pilih Tanggal...">
                </div>
            </div>

            {{-- Tanggal Selesai --}}
            <div class="group">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Sampai Tanggal</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-calendar-check"></i></span>
                    <input type="text" name="tanggal_selesai" id="tanggal_selesai" required
                           value="{{ $inputs['tanggal_selesai'] ?? now()->endOfMonth()->format('Y-m-d') }}"
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none shadow-sm cursor-pointer bg-white"
                           placeholder="Pilih Tanggal...">
                </div>
            </div>

            {{-- Jumlah Cluster --}}
            <div class="group">
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Jumlah Kelompok (Cluster) <span class="text-xs font-normal text-gray-500 ml-1">(Max: {{ $maxK ?? 10 }})</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-layer-group"></i></span>
                    <input type="number" name="jumlah_cluster" min="2" max="{{ $maxK ?? 10 }}" required
                           value="{{ $inputs['jumlah_cluster'] ?? 3 }}"
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none shadow-sm">
                </div>
            </div>

            {{-- Tombol Submit --}}
            <div>
                <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg active:scale-95 transform transition-all duration-200 flex justify-center items-center h-[46px]">
                    <i class="fas fa-rocket mr-2"></i> Mulai Analisis
                </button>
            </div>
        </div>
    </form>


    {{-- HASIL ANALISIS --}}
    @if(isset($results) && $results)

    {{-- 1. VISUALISASI CHARTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        {{-- Pie Chart --}}
        <div class="bg-white border border-gray-100 shadow-lg rounded-xl overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col h-[500px]">
            <div class="p-5 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-800 flex items-center"><i class="fas fa-chart-pie mr-2 text-indigo-500"></i> Kontribusi Omset per Kelompok</h3>
            </div>
            <div class="p-6 flex-grow relative flex items-center justify-center">
                <div class="chart-container"><canvas id="clusterPieChart"></canvas></div>
            </div>
        </div>

        {{-- Scatter Chart --}}
        <div class="bg-white border border-gray-100 shadow-lg rounded-xl overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col h-[500px]">
            <div class="p-5 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-800 flex items-center"><i class="fas fa-chart-scatter mr-2 text-indigo-500"></i> Peta Persebaran Kategori</h3>
            </div>
            <div class="p-6 flex-grow relative">
                <div class="chart-container"><canvas id="kmeansChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- 2. TABEL CENTROID --}}
    <div class="bg-white border border-gray-100 shadow-lg rounded-xl overflow-hidden mb-10">
        <div class="p-6 border-b border-gray-100 bg-indigo-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-indigo-900">Kesimpulan Karakteristik Kelompok</h3>
                <p class="text-sm text-indigo-600 mt-1">Rata-rata kinerja penjualan pada setiap kelompok yang terbentuk.</p>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-6 py-4 text-left w-1/4">Kelompok (Cluster)</th>
                        <th class="px-6 py-4 text-left">Rata-rata Penjualan</th>
                        <th class="px-6 py-4 text-left">Rata-rata Pendapatan</th>
                        <th class="px-6 py-4 text-left w-1/3">Rekomendasi Strategis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($results['centroids'] as $centroid)
                        <tr class="hover:bg-indigo-50 transition-colors duration-150">
                            <td class="px-6 py-4 font-bold text-gray-800 flex items-center">
                                <span class="w-4 h-4 rounded-full mr-3 shadow-sm" style="background-color: {{ $centroid['color'] ?? '#ccc' }}"></span>
                                {{ $centroid['label'] }}
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                <i class="fas fa-box text-gray-400 mr-1"></i> {{ number_format($centroid['avg_kuantitas'], 0) }} Unit
                            </td>
                            <td class="px-6 py-4 text-gray-700 font-bold">
                                Rp {{ number_format($centroid['avg_omset'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 leading-relaxed">
                                @if($loop->first) 
                                    <span class="text-yellow-600 font-semibold"><i class="fas fa-star mr-1"></i> Produk Unggulan.</span> Stok harus selalu tersedia.
                                @elseif($loop->last) 
                                    <span class="text-red-500 font-semibold"><i class="fas fa-exclamation-circle mr-1"></i> Performa Rendah.</span> Evaluasi ulang.
                                @else 
                                    <span class="text-blue-600 font-semibold"><i class="fas fa-check-circle mr-1"></i> Stabil.</span> Pertahankan. 
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. DETAIL ANGGOTA CLUSTER (UPDATED: KOLOM TERPISAH & LIST SEMUA PRODUK) --}}
    <div class="bg-white border border-gray-100 shadow-lg rounded-xl overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Detail Anggota per Kategori</h3>
            <p class="text-sm text-gray-500 mt-1">Daftar kategori barang yang masuk ke dalam setiap kelompok analisis.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-6 py-4 text-left w-1/5">Kategori Barang</th>
                        <th class="px-6 py-4 text-center">Terjual (Unit)</th>
                        <th class="px-6 py-4 text-right">Total Omset</th>
                        <th class="px-6 py-4 text-left w-2/5">Daftar Produk Terlaris</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($results['anggota'] as $member)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                            
                            {{-- KOLOM 1: KATEGORI --}}
                            <td class="px-6 py-4 align-top">
                                <div class="font-bold text-gray-800 text-base">{{ $member['nama_kategori'] }}</div>
                                <div class="text-xs text-gray-400 mt-1 font-mono">ID: #{{ $member['id_kategori'] }}</div>
                            </td>

                            {{-- KOLOM 2: TERJUAL (UNIT) --}}
                            <td class="px-6 py-4 align-top text-center">
                                <span class="inline-block font-bold text-gray-700 bg-gray-100 border border-gray-200 px-3 py-1 rounded shadow-sm">
                                    {{ number_format($member['kuantitas'], 0) }}
                                </span>
                            </td>

                            {{-- KOLOM 3: OMSET (RP) --}}
                            <td class="px-6 py-4 align-top text-right">
                                <span class="font-bold text-green-600">
                                    Rp {{ number_format($member['omset'], 0, ',', '.') }}
                                </span>
                            </td>

                            {{-- KOLOM 4: DAFTAR PRODUK (SEMUA) --}}
                            <td class="px-6 py-4 align-top">
                                @if ($member['top_barang']->isNotEmpty())
                                    <div class="max-h-60 overflow-y-auto custom-scrollbar pr-2">
                                        <ul class="text-xs text-gray-600 space-y-2">
                                            @foreach ($member['top_barang'] as $index => $barang)
                                                <li class="flex items-start border-b border-gray-100 pb-1 last:border-0">
                                                    <span class="mr-2 text-indigo-500 font-bold w-4 text-right">{{ $index + 1 }}.</span>
                                                    <div class="flex-1">
                                                        <span class="block text-gray-800 font-medium">{{ $barang->nama_barang }}</span>
                                                        <span class="text-[10px] text-gray-400">Terjual: <strong>{{ $barang->total_jual }}</strong> Unit</span>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else 
                                    <span class="text-xs text-gray-400 italic flex items-center">
                                        <i class="fas fa-info-circle mr-1"></i> Belum ada transaksi barang.
                                    </span> 
                                @endif
                            </td>

                            {{-- KOLOM 5: STATUS CLUSTER --}}
                            <td class="px-6 py-4 align-top text-center">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-gray-200 shadow-sm text-gray-700">
                                    {{ $member['label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-search text-3xl mb-3 text-gray-300"></i>
                                    Tidak ada data yang cocok untuk dianalisis.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @else
        {{-- EMPTY STATE --}}
        <div class="bg-indigo-50 border-2 border-dashed border-indigo-200 rounded-xl p-10 text-center">
            <i class="fas fa-chart-line text-5xl text-indigo-300 mb-4"></i>
            <h3 class="text-xl font-bold text-indigo-800 mb-2">Siap Menganalisis Data?</h3>
            <p class="text-indigo-600">Tentukan tanggal periode dan jumlah kelompok di atas, lalu tekan tombol <strong>Mulai Analisis</strong>.</p>
        </div>
    @endif

</div>

{{-- SCRIPT --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script>
    // INIT FLATPICKR INDONESIA
    document.addEventListener("DOMContentLoaded", function() {
        const config = {
            dateFormat: "Y-m-d", // Simpan ke server
            altInput: true,      // Tampilan User
            altFormat: "j F Y",  // Format: 8 Desember 2025
            locale: "id",        // Bahasa Indonesia
            maxDate: "today",
            allowInput: true
        };
        flatpickr("#tanggal_mulai", config);
        flatpickr("#tanggal_selesai", config);
    });

    @if(isset($results['chartData']) && $results['chartData'])
    // SCATTER CHART
    const scatterCtx = document.getElementById('kmeansChart').getContext('2d');
    const scatterChartData = {!! json_encode($results['chartData']) !!};
    new Chart(scatterCtx, {
        type: 'scatter',
        data: { datasets: scatterChartData },
        options: {
            responsive: true, maintainAspectRatio: false, layout: { padding: 20 },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)', titleColor: '#1f2937', bodyColor: '#4b5563', borderColor: '#e5e7eb', borderWidth: 1, padding: 10,
                    callbacks: {
                        label: function(context) {
                            const dataPoint = context.dataset.data[context.dataIndex];
                            let label = dataPoint.kategori || '';
                            const omsetFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(dataPoint.y);
                            if (label) label += ': ';
                            if (dataPoint.kategori === 'Centroid') return `🎯 PUSAT CLUSTER ${dataPoint.clusterId}`;
                            return `${label} ${dataPoint.x} Unit | ${omsetFormatted}`;
                        }
                    }
                }
            },
            scales: {
                x: { type: 'linear', position: 'bottom', title: { display: true, text: 'Total Kuantitas Terjual (Unit)', color: '#6b7280', font: { weight: 'bold' } }, grid: { color: '#f3f4f6' } },
                y: { type: 'linear', title: { display: true, text: 'Total Omset (Rp)', color: '#6b7280', font: { weight: 'bold' } }, grid: { color: '#f3f4f6' }, ticks: { callback: function(value) { return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt'; } } }
            }
        }
    });

    // PIE CHART
    const pieCtx = document.getElementById('clusterPieChart').getContext('2d');
    const pieChartData = {!! json_encode($results['pieChartData']) !!};
    const pieLabels = pieChartData.map(d => d.label);
    const omsetData = pieChartData.map(d => d.omset);
    const colors = pieChartData.map(d => d.color.replace('0.8', '1'));

    new Chart(pieCtx, {
        type: 'doughnut',
        data: { labels: pieLabels, datasets: [{ data: omsetData, backgroundColor: colors, borderColor: '#ffffff', borderWidth: 3, hoverOffset: 15 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)', bodyColor: '#1f2937', borderColor: '#e5e7eb', borderWidth: 1, padding: 12,
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1) + '%';
                            const omset = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
                            return ` ${context.label}: ${omset} (${percentage})`;
                        }
                    }
                }
            }
        }
    });
    @endif
</script>

@endsection