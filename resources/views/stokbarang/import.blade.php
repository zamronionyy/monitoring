@extends('layouts.app') 

@section('title', 'Import Stok Barang')

@section('content')

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
    .upload-area:hover {
        border-color: #4f46e5; /* Indigo-600 */
        background-color: #f5f3ff; /* Indigo-50 */
    }
</style>

<div class="max-w-3xl mx-auto py-6 animate-fade-in-up">

    {{-- HEADER --}}
    <div class="flex items-center mb-8">
        <div class="bg-indigo-100 text-indigo-600 p-3 rounded-lg mr-4 shadow-sm shrink-0">
            <i class="fas fa-dolly-flatbed text-2xl"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Import Data Stok Masuk</h2>
            <p class="text-gray-500 text-sm">Upload riwayat stok masuk secara massal menggunakan Excel.</p>
        </div>
    </div>

    {{-- NOTIFIKASI ERROR/SUCCESS --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
             class="mb-6 px-4 py-4 rounded-lg bg-green-50 border-l-4 border-green-500 text-green-800 shadow-sm flex items-start">
            <i class="fas fa-check-circle mt-1 mr-3 text-lg shrink-0"></i>
            <div>
                <strong class="font-bold">Berhasil!</strong>
                <p class="text-sm mt-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 px-4 py-4 rounded-lg bg-red-50 border-l-4 border-red-500 text-red-800 shadow-sm flex items-start">
            <i class="fas fa-times-circle mt-1 mr-3 text-lg shrink-0"></i>
            <div>
                <strong class="font-bold">Gagal Import!</strong>
                <p class="text-sm mt-1">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 px-4 py-4 rounded-lg bg-red-50 border-l-4 border-red-500 text-red-800 shadow-sm">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle mt-1 mr-3 text-lg shrink-0"></i>
                <div>
                    <strong class="font-bold">Terjadi Kesalahan Validasi:</strong>
                    <ul class="list-disc list-inside text-sm mt-2 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
        
        {{-- LANGKAH 1: DOWNLOAD TEMPLATE --}}
        <div class="p-8 bg-yellow-50 border-b border-yellow-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-yellow-200 text-yellow-800 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0">1</div>
                <div>
                    <h4 class="font-bold text-yellow-900">Persiapkan File</h4>
                    <p class="text-sm text-yellow-800">Unduh template agar format kolom sesuai.</p>
                </div>
            </div>
            <a href="{{ route('stokbarang.template') }}" 
               class="whitespace-nowrap bg-white text-yellow-700 border border-yellow-200 hover:bg-yellow-500 hover:text-white hover:border-transparent px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all duration-200 flex items-center group">
                <i class="fas fa-download mr-2 group-hover:animate-bounce"></i> Download Template
            </a>
        </div>

        {{-- LANGKAH 2: FORM UPLOAD --}}
        <div class="p-8">
            <form action="{{ route('stokbarang.import.excel') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf

                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-indigo-100 text-indigo-700 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0">2</div>
                    <h4 class="font-bold text-gray-800">Upload File Excel</h4>
                </div>

                {{-- CUSTOM FILE INPUT (Drag & Drop Look) --}}
                <div class="relative group">
                    <div class="upload-area w-full h-48 rounded-xl border-2 border-dashed border-gray-300 flex flex-col justify-center items-center cursor-pointer transition-all duration-300 relative bg-gray-50">
                        
                        {{-- Input File Hidden --}}
                        <input type="file" name="file_stok" id="file_stok" 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                               required onchange="updateFileName(this)">
                        
                        {{-- Visual Placeholder --}}
                        <div class="text-center p-4 transition-all duration-300 group-hover:scale-105" id="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3 group-hover:text-indigo-500 transition-colors"></i>
                            <p class="text-gray-600 font-medium">Klik atau Seret file Excel ke sini</p>
                            <p class="text-xs text-gray-400 mt-1">Format: .xlsx, .xls, atau .csv</p>
                        </div>

                        {{-- Visual Saat File Dipilih --}}
                        <div class="hidden text-center p-4" id="file-info">
                            <i class="fas fa-file-excel text-4xl text-green-500 mb-3"></i>
                            <p class="text-gray-800 font-bold text-lg" id="file-name-display">filename.xlsx</p>
                            <p class="text-green-600 text-sm mt-1">File siap diupload!</p>
                            <button type="button" onclick="resetFile()" class="mt-3 text-red-500 text-xs hover:underline z-20 relative">Ganti File</button>
                        </div>
                    </div>
                    @error('file_stok')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- INSTRUKSI KOLOM --}}
                <div class="mt-6 bg-blue-50 border border-blue-100 rounded-lg p-4">
                    <h5 class="font-bold text-blue-800 text-sm mb-2 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i> Ketentuan Data:
                    </h5>
                    <ul class="text-xs text-blue-900 space-y-1 list-disc list-inside">
                        <li>Kolom wajib: <strong>kode_barang</strong>, <strong>stok</strong>, <strong>tanggal_masuk</strong>.</li>
                        <li><strong>Kode Barang</strong> harus sudah terdaftar di Master Barang.</li>
                        <li>Format tanggal disarankan: <code>YYYY-MM-DD</code> (Contoh: 2024-01-30).</li>
                    </ul>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-5">
                    <a href="{{ route('stokbarang.index') }}" class="px-5 py-2.5 rounded-lg text-gray-600 font-medium hover:bg-gray-100 hover:text-gray-800 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-bold shadow-md hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center">
                        <i class="fas fa-file-import mr-2"></i> Proses Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT INTERAKTIVITAS --}}
<script>
    function updateFileName(input) {
        const placeholder = document.getElementById('upload-placeholder');
        const fileInfo = document.getElementById('file-info');
        const fileNameDisplay = document.getElementById('file-name-display');

        if (input.files && input.files[0]) {
            placeholder.classList.add('hidden');
            fileInfo.classList.remove('hidden');
            fileNameDisplay.innerText = input.files[0].name;
            
            const parent = input.parentElement;
            parent.classList.remove('border-dashed', 'border-gray-300');
            parent.classList.add('border-solid', 'border-green-400', 'bg-green-50');
        }
    }

    function resetFile() {
        const input = document.getElementById('file_stok');
        input.value = '';
        
        const placeholder = document.getElementById('upload-placeholder');
        const fileInfo = document.getElementById('file-info');
        
        placeholder.classList.remove('hidden');
        fileInfo.classList.add('hidden');
        
        const parent = input.parentElement;
        parent.classList.add('border-dashed', 'border-gray-300');
        parent.classList.remove('border-solid', 'border-green-400', 'bg-green-50');
    }
</script>
@endsection