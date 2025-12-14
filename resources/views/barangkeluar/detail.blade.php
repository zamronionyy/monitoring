@extends('layouts.app')

@section('title', 'Detail Transaksi - ' . $transaksi->id_transaksi)

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translate3d(0, 20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
    .animate-fade-in-up { animation-name: fadeInUp; animation-duration: 0.5s; animation-fill-mode: forwards; }
</style>

<div class="max-w-4xl mx-auto opacity-0 animate-fade-in-up">

    {{-- NAVIGATION --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <a href="{{ route('barangkeluar.index') }}" class="text-gray-600 hover:text-indigo-600 font-medium flex items-center transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Riwayat
        </a>
        <div class="flex gap-2">
            <a href="{{ route('barangkeluar.download-pdf', $transaksi->id) }}" class="bg-red-600 text-white px-5 py-2 rounded-lg font-bold shadow-md hover:bg-red-700 hover:shadow-lg transform active:scale-95 transition-all duration-200 flex items-center">
                <i class="fas fa-file-pdf mr-2"></i> Download PDF
            </a>
            <a href="{{ route('barangkeluar.print', $transaksi->id) }}" target="_blank" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-bold shadow-md hover:bg-indigo-700 hover:shadow-lg transform active:scale-95 transition-all duration-200 flex items-center">
                <i class="fas fa-print mr-2"></i> Cetak Nota
            </a>
        </div>
    </div>

    {{-- KERTAS NOTA --}}
    <div class="bg-white shadow-xl rounded-xl border border-gray-200 overflow-hidden relative">
        <div class="h-2 bg-gradient-to-r from-indigo-500 to-purple-500 w-full"></div>
        
        <div class="p-8 sm:p-10">
            
            {{-- HEADER / KOP SURAT --}}
            <div class="flex flex-col sm:flex-row justify-between items-start border-b-2 border-gray-800 pb-6 mb-8">
                <div class="flex items-center mb-4 sm:mb-0">
                    @php 
                        $pathLogo = storage_path('app/public/image/logo.png'); 
                        if (!file_exists($pathLogo)) { $pathLogo = public_path('image/logo.png'); } 
                        if (!file_exists($pathLogo)) { $pathLogo = public_path('storage/image/logo.png'); } 
                    @endphp
                    @if(file_exists($pathLogo)) 
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($pathLogo)) }}" alt="Logo" class="h-20 w-auto object-contain mr-5"> 
                    @else 
                        <div class="h-16 w-16 bg-indigo-100 text-indigo-600 flex items-center justify-center rounded-lg mr-5 shadow-sm"><i class="fas fa-store text-3xl"></i></div> 
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">CV. BIMA PERAGA NUSANTARA</h1>
                        <p class="text-sm text-gray-600 mt-1">Gg. Tower 4A Unggahan, Banjarangung, Mojokerto</p>
                        <p class="text-sm text-gray-500">Telp: 0321-330850 | Email: bimaperaga.com</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-block border-2 border-gray-800 px-4 py-1 mb-2">
                        <h2 class="text-xl font-bold text-gray-900 uppercase tracking-widest">NOTA PENJUALAN</h2>
                    </div>
                    <p class="text-gray-500 text-sm font-mono">ID: #{{ $transaksi->id_transaksi }}</p>
                </div>
            </div>

            {{-- INFORMASI TRANSAKSI --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Informasi Transaksi</h3>
                    <table class="w-full text-sm">
                        <tr>
                            <td class="font-semibold text-gray-700 py-1 w-24">No. Nota</td>
                            <td class="text-gray-900 font-bold">: {{ $transaksi->id_transaksi }}</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700 py-1">Tanggal</td>
                            <td class="text-gray-900">: {{ \Carbon\Carbon::parse($transaksi->tanggal)->locale('id')->isoFormat('D MMMM Y') }}</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700 py-1">Kasir</td>
                            <td class="text-gray-900">: {{ $transaksi->user->name ?? 'Admin' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Kepada Yth.</h3>
                    <table class="w-full text-sm">
                        <tr>
                            <td class="font-semibold text-gray-700 py-1 w-24">Nama</td>
                            <td class="text-gray-900 font-bold">: {{ $transaksi->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700 py-1">Alamat</td>
                            <td class="text-gray-900">: {{ $transaksi->pelanggan->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700 py-1">Telp</td>
                            <td class="text-gray-900">: {{ $transaksi->pelanggan->no_telp ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- TABEL BARANG --}}
            <div class="overflow-hidden border border-gray-200 rounded-lg mb-8">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase border-r border-gray-200">No</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase border-r border-gray-200">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase border-r border-gray-200">Nama Barang</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase border-r border-gray-200">Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase border-r border-gray-200">Harga</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($transaksi->detailBarangKeluars as $i=>$d)
                        <tr>
                            <td class="px-6 py-3 text-center text-sm text-gray-500 border-r border-gray-100">{{ $i+1 }}</td>
                            <td class="px-6 py-3 text-center font-mono text-sm text-gray-600 border-r border-gray-100">{{ $d->barang->kode_barang ?? '-' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-900 font-medium border-r border-gray-100">{{ $d->barang->nama_barang ?? 'Item Terhapus' }}</td>
                            <td class="px-6 py-3 text-center font-bold text-sm text-gray-800 border-r border-gray-100">{{ $d->jumlah }}</td>
                            <td class="px-6 py-3 text-right text-sm text-gray-600 border-r border-gray-100">Rp {{ number_format($d->harga_satuan,0,',','.') }}</td>
                            <td class="px-6 py-3 text-right font-bold text-sm text-gray-900">Rp {{ number_format($d->total_harga,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- FOOTER & TOTALS --}}
            <div class="flex flex-col md:flex-row justify-between items-start gap-8">
                {{-- Bagian Catatan (Dirapikan) --}}
                <div class="w-full md:w-1/2">
                    <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-4">
                        <h4 class="text-xs font-bold text-yellow-700 uppercase mb-2">
                            <i class="fas fa-info-circle mr-1"></i> Catatan:
                        </h4>
                        <ul class="text-xs text-yellow-800 list-disc list-inside space-y-2 italic">
                            <li>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</li>
                            <li>Pembayaran via Transfer:
                                <div class="mt-1 ml-4 not-italic text-yellow-900 font-medium border-l-2 border-yellow-300 pl-3">
                                    <p class="font-bold">Rekening A.n Sri Winanti</p>
                                    <div class="grid grid-cols-[60px_1fr] gap-x-2 mt-1">
                                        <span>Mandiri</span> <span>: 142 0021001023</span>
                                        <span>BCA</span>     <span>: 0501188882</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Bagian Total --}}
                <div class="w-full md:w-5/12 bg-gray-50 rounded-lg border border-gray-200 p-6 space-y-3">
                    <div class="flex justify-between items-center text-sm border-b border-gray-300 pb-2">
                        <span class="text-gray-600">Total Barang</span>
                        <span class="font-bold text-gray-800">Rp {{ number_format($transaksi->total_harga - $transaksi->biaya_kirim, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-gray-300 pb-2">
                        <span class="text-gray-600">Biaya Kirim</span>
                        <span class="font-bold text-gray-800">Rp {{ number_format($transaksi->biaya_kirim, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-gray-300 pb-3">
                        <span class="font-semibold text-gray-600 uppercase">Uang Muka (DP)</span>
                        <span class="font-bold text-red-600">- Rp {{ number_format($transaksi->uang_muka, 0, ',', '.') }}</span>
                    </div>
                    
                    @php $sisa = $transaksi->total_harga - $transaksi->uang_muka; @endphp
                    <div class="flex justify-between items-center pt-1">
                        <span class="font-bold text-gray-800 uppercase">Total Tagihan</span>
                        <span class="font-extrabold text-xl {{ $sisa > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format($sisa, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- TANDA TANGAN --}}
            <div class="mt-16 grid grid-cols-2 gap-8 text-center">
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-16">Tanda Terima,</p>
                    <p class="text-sm font-bold text-gray-900 border-t border-gray-300 inline-block px-8 pt-2">
                        ( {{ $transaksi->pelanggan->nama_pelanggan ?? '....................' }} )
                    </p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-16">Hormat Kami,</p>
                    <p class="text-sm font-bold text-gray-900 border-t border-gray-300 inline-block px-8 pt-2">
                        ( {{ $transaksi->user->name ?? 'Admin' }} )
                    </p>
                </div>
            </div>
        </div>
        
        {{-- WATERMARK --}}
        <div class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-[0.03]">
            <i class="fas fa-file-invoice text-[400px]"></i>
        </div>
    </div>
</div>
@endsection