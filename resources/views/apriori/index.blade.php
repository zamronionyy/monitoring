@extends('layouts.app') 

@section('title', 'Analisis Pola Beli (Apriori)')

@section('content')

{{-- LIBRARY: Chart.js, Flatpickr, SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- STYLE TAMBAHAN --}}
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translate3d(0, 20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
    .animate-fade-in-up { animation-name: fadeInUp; animation-duration: 0.5s; animation-fill-mode: forwards; }
    .flatpickr-input[readonly] { background-color: white !important; cursor: pointer; }
    .chart-container { position: relative; height: 400px; width: 100%; }
</style>

<div class="max-w-7xl mx-auto py-6 animate-fade-in-up">

    {{-- HEADER HALAMAN --}}
    <div class="flex items-center gap-4 mb-8">
        <div class="bg-purple-100 text-purple-600 w-12 h-12 rounded-lg flex items-center justify-center shadow-sm shrink-0">
            <i class="fas fa-project-diagram text-xl"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Analisis Pola Beli (Apriori)</h2>
            <p class="text-gray-500 text-sm">Temukan barang apa yang sering dibeli bersamaan oleh pelanggan untuk strategi bundling.</p>
        </div>
    </div>

    {{-- ALERT ERROR --}}
    @if (session('error'))
        <div class="mb-6 px-4 py-4 rounded-lg bg-red-50 border-l-4 border-red-500 text-red-800 shadow-sm flex items-start">
            <i class="fas fa-times-circle mt-1 mr-3 text-lg shrink-0"></i>
            <div><strong class="font-bold">Gagal:</strong><p class="text-sm mt-1">{{ session('error') }}</p></div>
        </div>
    @endif

    {{-- FORM PARAMETER --}}
    <form id="aprioriForm" action="{{ route('apriori.run') }}" method="GET" 
          class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100 mb-10 hover:shadow-xl transition-shadow duration-300">
        @csrf
        
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h3 class="font-bold text-gray-800 flex items-center">
                <i class="fas fa-sliders-h mr-2 text-purple-500"></i> Parameter Analisis
            </h3>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
            {{-- Input Tanggal Mulai --}}
            <div class="group">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Dari Tanggal <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="tanggal_mulai" id="tanggal_mulai" required
                           value="{{ $inputs['tanggal_mulai'] ?? now()->startOfMonth()->format('Y-m-d') }}"
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-purple-500 outline-none shadow-sm cursor-pointer bg-white"
                           placeholder="Pilih Tanggal...">
                </div>
            </div>

            {{-- Input Tanggal Selesai --}}
            <div class="group">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Sampai Tanggal <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-calendar-check"></i></span>
                    <input type="text" name="tanggal_selesai" id="tanggal_selesai" required
                           value="{{ $inputs['tanggal_selesai'] ?? now()->endOfMonth()->format('Y-m-d') }}"
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-purple-500 outline-none shadow-sm cursor-pointer bg-white"
                           placeholder="Pilih Tanggal...">
                </div>
            </div>

            {{-- Min Support --}}
            <div class="group">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Min. Support (%)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-percentage"></i></span>
                    <input type="number" name="min_support" step="1" min="1" max="100" required
                           value="{{ $inputs['min_support'] ?? 10 }}"
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-purple-500 outline-none shadow-sm">
                </div>
            </div>

            {{-- Min Confidence --}}
            <div class="group">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Min. Confidence (%)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-check-double"></i></span>
                    <input type="number" name="min_confidence" step="1" min="1" max="100" required
                           value="{{ $inputs['min_confidence'] ?? 50 }}"
                           class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-purple-500 outline-none shadow-sm">
                </div>
            </div>

            {{-- Tombol Analisis --}}
            <div>
                <button type="submit" 
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg active:scale-95 transform transition-all duration-200 flex justify-center items-center h-[46px]">
                    <i class="fas fa-search mr-2"></i> Analisis
                </button>
            </div>
        </div>
    </form>

    {{-- HASIL ANALISIS --}}
    @if(isset($results) && $results)
    
        {{-- 1. GRAFIK LINE CHART (Diagram Garis) --}}
        <div class="bg-white border border-gray-100 shadow-lg rounded-xl overflow-hidden mb-10 hover:shadow-xl transition-shadow duration-300">
            <div class="p-6 border-b border-gray-100 bg-purple-50 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-gray-800 flex items-center"><i class="fas fa-chart-line mr-2 text-purple-500"></i> Grafik Perbandingan Pola</h3>
                    <p class="text-xs text-purple-600 mt-1">Perbandingan antara seberapa sering muncul (Support) vs seberapa kuat hubungannya (Confidence).</p>
                </div>
                <span class="text-xs bg-white text-purple-700 px-3 py-1 rounded-full font-bold border border-purple-200 shadow-sm">
                    {{ count($results['rules']) }} Pola Ditemukan
                </span>
            </div>
            <div class="p-6">
                <div class="chart-container"><canvas id="aprioriLineChart"></canvas></div>
            </div>
        </div>

        {{-- 2. TABEL HASIL (LENGKAP & MUDAH DIMENGERTI) --}}
        <div class="bg-white border border-gray-100 shadow-lg rounded-xl overflow-hidden mb-10">
            <div class="p-6 border-b border-gray-100 bg-purple-50">
                <h3 class="font-bold text-purple-900">Detail Aturan & Rekomendasi</h3>
                <p class="text-sm text-purple-600 mt-1">
                    Berdasarkan analisis dari <strong>{{ number_format($results['total_transaksi']) }}</strong> transaksi yang valid.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-6 py-4 text-left w-10">No</th>
                            <th class="px-6 py-4 text-left w-1/4">Pola Kombinasi Barang</th>
                            <th class="px-6 py-4 text-center">Support</th>
                            <th class="px-6 py-4 text-center">Confidence</th>
                            <th class="px-6 py-4 text-center">Lift (Kekuatan)</th>
                            <th class="px-6 py-4 text-left w-1/3">Rekomendasi Bisnis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($results['rules'] as $index => $rule)
                            <tr class="hover:bg-purple-50 transition-colors duration-150">
                                <td class="px-6 py-4 text-center font-medium text-gray-500">{{ $index + 1 }}</td>
                                
                                {{-- Pola Barang --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center">
                                            <span class="text-[10px] uppercase font-bold text-gray-400 w-16">Jika Beli</span>
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold border border-blue-200">{{ $rule['antecedent_name'] }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-[10px] uppercase font-bold text-gray-400 w-16">Maka Beli</span>
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-bold border border-green-200">{{ $rule['consequent_name'] }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Support --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="block font-bold text-gray-700 text-base">{{ $rule['support'] }}%</span>
                                    <span class="text-[10px] text-gray-400">Frekuensi</span>
                                </td>

                                {{-- Confidence --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="block font-bold text-purple-700 text-base">{{ $rule['confidence'] }}%</span>
                                    <span class="text-[10px] text-gray-400">Peluang</span>
                                </td>

                                {{-- Lift Ratio --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="text-base font-extrabold font-mono {{ $rule['lift'] > 1 ? 'text-green-600' : 'text-gray-400' }}">
                                            {{ $rule['lift'] }}x
                                        </span>
                                        <span class="text-[10px] uppercase font-bold text-gray-400">
                                            {{ $rule['lift'] > 1 ? 'Dominan' : 'Normal' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Rekomendasi (Bahasa Manusia) --}}
                                <td class="px-6 py-4">
                                    @if ($rule['lift'] > 1.2)
                                        <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                                            <p class="text-green-800 text-xs font-medium mb-1"><i class="fas fa-star mr-1"></i> <strong>Kombinasi Sangat Kuat!</strong></p>
                                            <p class="text-gray-600 text-xs">Pelanggan sangat sering membeli kedua barang ini bersamaan. Buatlah <strong>Paket Bundling</strong> untuk meningkatkan omset.</p>
                                        </div>
                                    @elseif ($rule['lift'] > 1.0)
                                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                                            <p class="text-blue-800 text-xs font-medium mb-1"><i class="fas fa-thumbs-up mr-1"></i> <strong>Hubungan Positif</strong></p>
                                            <p class="text-gray-600 text-xs">Saat pelanggan mengambil <em>{{ $rule['antecedent_name'] }}</em>, kasir disarankan menawarkan <em>{{ $rule['consequent_name'] }}</em>.</p>
                                        </div>
                                    @else
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                            <p class="text-gray-500 text-xs italic">Hubungan antar barang ini lemah atau hanya kebetulan.</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-search-minus text-3xl mb-2 text-gray-300"></i>
                                        Tidak ada pola yang memenuhi kriteria minimum (Support & Confidence). <br>
                                        Coba turunkan nilai persentase pada form di atas.
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
        <div class="bg-purple-50 border-2 border-dashed border-purple-200 rounded-xl p-10 text-center">
            <div class="inline-block bg-purple-100 p-4 rounded-full mb-4">
                <i class="fas fa-search-dollar text-4xl text-purple-500"></i>
            </div>
            <h3 class="text-xl font-bold text-purple-900 mb-2">Siap Menganalisis Data?</h3>
            <p class="text-purple-700 max-w-lg mx-auto">
                Masukkan rentang tanggal dan parameter algoritma di atas, lalu tekan tombol <strong>Analisis</strong> untuk menemukan pola belanja pelanggan Anda.
            </p>
        </div>
    @endif

</div>

{{-- SCRIPTS --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script>
    // 1. FLATPICKR INDONESIA
    document.addEventListener("DOMContentLoaded", function() {
        const config = {
            dateFormat: "Y-m-d", altInput: true, altFormat: "j F Y", locale: "id", maxDate: "today", allowInput: true
        };
        flatpickr("#tanggal_mulai", config);
        flatpickr("#tanggal_selesai", config);

        // Validasi Form
        document.getElementById('aprioriForm').addEventListener('submit', function(e) {
            const start = document.getElementById('tanggal_mulai').value;
            const end = document.getElementById('tanggal_selesai').value;
            if(!start || !end) {
                e.preventDefault();
                Swal.fire({icon: 'warning', title: 'Tanggal Kosong', text: 'Harap isi rentang tanggal terlebih dahulu.', confirmButtonColor: '#9333ea'});
            }
        });
    });

    // 2. CHART JS (LINE CHART)
    @if(isset($results['rules']) && count($results['rules']) > 0)
    document.addEventListener('DOMContentLoaded', function () {
        const rules = @json($results['rules']);
        
        // Label Sumbu X (Nama Pola)
        const labels = rules.map((r, i) => `Pola ${i+1}`);
        
        // Data Sumbu Y
        const supportData = rules.map(r => r.support);
        const confidenceData = rules.map(r => r.confidence);

        const ctx = document.getElementById('aprioriLineChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line', 
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Kekuatan Hubungan (Confidence %)',
                        data: confidenceData,
                        borderColor: '#9333ea', // Ungu
                        backgroundColor: 'rgba(147, 51, 234, 0.1)',
                        borderWidth: 2,
                        tension: 0.3, 
                        pointRadius: 5,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#9333ea',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Frekuensi Muncul (Support %)',
                        data: supportData,
                        borderColor: '#10b981', // Hijau
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 5,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#10b981',
                        borderDash: [5, 5], // Garis putus-putus
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, padding: 20 } },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#1f2937', bodyColor: '#4b5563', borderColor: '#e5e7eb', borderWidth: 1, padding: 12,
                        callbacks: {
                            title: function(context) {
                                const idx = context[0].dataIndex;
                                return `${rules[idx].antecedent_name} ➔ ${rules[idx].consequent_name}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true, max: 100,
                        title: { display: true, text: 'Persentase (%)', color: '#6b7280', font: { weight: 'bold' } },
                        grid: { color: '#f3f4f6' }
                    },
                    x: {
                        display: true,
                        title: { display: true, text: 'Daftar Aturan (Pola ke-)', color: '#6b7280' },
                        grid: { display: false }
                    }
                }
            }
        });
    });
    @endif
</script>

@endsection