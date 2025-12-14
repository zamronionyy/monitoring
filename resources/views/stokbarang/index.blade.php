@extends('layouts.app') 

@section('title', 'Daftar Stok Barang')

@section('content')

{{-- STYLE ANIMASI & VISUAL --}}
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
    /* Checkbox Custom Pointer */
    input[type="checkbox"] { cursor: pointer; }
    /* Custom Scrollbar untuk Dropdown */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>

<div class="animate-fade-in-up">

    {{-- HEADER & TOOLS --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">

        {{-- GRUP TOMBOL AKSI (KIRI) --}}
        <div class="flex flex-wrap items-center gap-2">
            @if(in_array(auth()->user()->role, ['admin', 'gudang']))
                <a href="{{ route('stokbarang.create') }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-blue-700 hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center">
                       <i class="fas fa-plus mr-2"></i> Tambah Stok
                </a>
            @endif

            @if(auth()->user()->role == 'admin')
               
             <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button" 
                            class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-green-700 hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center">
                        <i class="fas fa-file-excel mr-2"></i> Excel 
                        <i class="fas fa-chevron-down ml-2 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute z-20 mt-2 w-48 rounded-lg shadow-xl bg-white border border-gray-100 ring-1 ring-black ring-opacity-5 focus:outline-none origin-top-left left-0 overflow-hidden">
                        <div class="py-1">
                            <a href="{{ route('stokbarang.import') }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <i class="fas fa-upload mr-3 text-gray-400 group-hover:text-blue-500"></i> Import Data
                            </a>
                            <a href="{{ route('stokbarang.export.excel') }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <i class="fas fa-download mr-3 text-gray-400 group-hover:text-blue-500"></i> Export Data
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Hapus Terpilih --}}
                <button type="submit" form="delete-multiple-stock-form" id="delete-multiple-stock-btn"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-red-700 hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
                        disabled onclick="return confirm('Anda yakin ingin menghapus catatan stok masuk yang terpilih? Aksi ini akan mempengaruhi stok akhir!');">
                       <i class="fas fa-trash-alt mr-2"></i> Hapus Terpilih
                </button>
            @endif
        </div>

        {{-- FORM PENCARIAN & FILTER (KANAN) --}}
        <form action="{{ route('stokbarang.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">

            {{-- Filter Kategori (CUSTOM DROPDOWN - ALPINE JS) --}}
            {{-- Tombol kotak (rounded-lg), Dropdown List membulat (rounded-xl) --}}
            <div x-data="{ 
                    open: false, 
                    selectedId: '{{ $selectedKategoriId }}',
                    selectedName: '{{ $selectedKategoriId ? ($kategoris->firstWhere('id', $selectedKategoriId)->nama_kategori ?? 'Semua Kategori') : 'Semua Kategori' }}',
                    select(id) {
                        document.getElementById('kategori_id').value = id;
                        document.getElementById('kategori_id').closest('form').submit();
                    }
                 }" 
                 class="relative min-w-[200px] sm:w-48 group"> 
                
                {{-- INPUT HIDDEN (ID tetap 'kategori_id' agar JS reset search di bawah tetap jalan) --}}
                <input type="hidden" name="kategori_id" id="kategori_id" value="{{ $selectedKategoriId }}">

                {{-- 1. TOMBOL TRIGGER (KOTAK / ROUNDED-LG) --}}
                <button type="button" @click="open = !open" @click.away="open = false"
                    class="flex items-center justify-between w-full pl-3 pr-3 bg-white border border-gray-300 
                           rounded-lg shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 
                           transition-all duration-200 h-10">
                    
                    <div class="flex items-center gap-2 text-gray-700 overflow-hidden">
                        <i class="fas fa-filter text-gray-500 text-sm flex-shrink-0"></i>
                        <span class="text-sm truncate" x-text="selectedName"></span>
                    </div>

                    <i class="fas fa-chevron-down text-xs text-gray-500 transition-transform duration-200 flex-shrink-0 ml-1" 
                       :class="{'rotate-180': open}"></i>
                </button>

                {{-- 2. DAFTAR PILIHAN (ROUNDED-XL / TIDAK LANCIP) --}}
                <div x-show="open" 
                     style="display: none;"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden origin-top-left">
                    
                    <div class="max-h-64 overflow-y-auto custom-scrollbar">
                        {{-- Opsi: Semua Kategori --}}
                        <div @click="select('')" 
                             class="px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 cursor-pointer transition-colors border-b border-gray-50 flex items-center justify-between">
                            <span>Semua Kategori</span>
                            <template x-if="!selectedId">
                                <i class="fas fa-check text-blue-500 text-xs"></i>
                            </template>
                        </div>

                        {{-- Loop Opsi Kategori --}}
                        @foreach ($kategoris as $kategori)
                            <div @click="select('{{ $kategori->id }}')" 
                                 class="px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 cursor-pointer transition-colors border-b border-gray-50 last:border-0 flex items-center justify-between">
                                <span>{{ $kategori->nama_kategori }}</span>
                                <template x-if="selectedId == '{{ $kategori->id }}'">
                                    <i class="fas fa-check text-blue-500 text-xs"></i>
                                </template>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Input Pencarian --}}
          {{-- Input Pencarian --}}
            <div class="relative flex items-center w-full sm:max-w-xs group">
                {{-- Icon Search --}}
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                    <i class="fas fa-search"></i>
                </div>
                
                {{-- PERBAIKAN: Ditambahkan autocomplete="off" untuk menghilangkan kotak hitam browser --}}
                <input type="text" name="search" id="search_input"
                        placeholder="Cari Kode, Nama..."
                        value="{{ request('search') }}"
                        autocomplete="off"
                        class="block w-full border border-gray-300 h-10 pl-10
                               @if(request('search')) pr-10 @else pr-4 @endif
                               focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg shadow-sm transition-all outline-none">
                
                {{-- Reset Button --}}
                @if(request('search'))
                    <button type="button" 
                            onclick="resetSearchAndKeepCategory()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 hover:scale-110 transition-all cursor-pointer"
                            title="Hapus Pencarian">
                        <i class="fas fa-times-circle"></i>
                    </button>
                @endif
            </div>

        </form>
    </div>

    {{-- NOTIFIKASI (Alpine JS) --}}
    @foreach (['success' => 'green', 'error' => 'red'] as $msg => $color)
        @if (session($msg))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-300" 
                 x-transition:leave-start="opacity-100 transform translate-y-0" 
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="bg-{{ $color }}-100 border-l-4 border-{{ $color }}-500 text-{{ $color }}-700 px-4 py-3 rounded shadow-md relative mb-4 flex items-center">
                <i class="fas fa-{{ $msg == 'success' ? 'check' : 'exclamation' }}-circle mr-2"></i>
                <span>{{ session($msg) }}</span>
            </div>
        @endif
    @endforeach

    {{-- TABEL DATA --}}
    <form id="delete-multiple-stock-form" action="{{ route('stokbarang.deleteMultiple') }}" method="POST">
        @csrf
        @method('DELETE')

        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">

            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-gray-200">
                            {{-- Checkbox Header --}}
                            <th class="px-5 py-3 text-left w-10">
                                <input type="checkbox" id="checkAllStock" 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-4 w-4 @if(auth()->user()->role != 'admin') hidden @endif">
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Barang</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori</th>

                            @if(auth()->user()->role == 'admin')
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Harga Jual</th>
                            @endif

                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Stok Masuk</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Masuk</th>
                            <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($stokBarangs as $item)
                        <tr class="hover:bg-blue-50 transition-colors duration-150 group">

                            {{-- Checkbox --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm">
                                <input type="checkbox" name="ids[]" value="{{ $item->id }}" 
                                       class="item-stock-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-4 w-4 @if(auth()->user()->role != 'admin') hidden @endif">
                            </td>

                            {{-- Kode --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm">
                                <span class="font-mono font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded text-xs">
                                    {{ $item->barang->kode_barang ?? 'N/A' }}
                                </span>
                            </td>

                            {{-- Nama --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm font-semibold text-gray-700">
                                {{ $item->barang->nama_barang ?? 'N/A' }}
                            </td>

                            {{-- Kategori --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm text-gray-600">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $item->barang->kategori->nama_kategori ?? 'N/A' }}
                                </span>
                            </td>

                            @if(auth()->user()->role == 'admin')
                            {{-- Harga --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm text-gray-700">
                                Rp {{ number_format($item->barang->harga ?? 0, 0, ',', '.') }}
                            </td>
                            @endif

                            {{-- Stok Masuk --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm font-bold text-green-600">
                                + {{ $item->stok }}
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm text-gray-600">
                                <i class="far fa-calendar-alt text-gray-400 mr-1"></i>
                                {{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d M Y') }}
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm text-center whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    {{-- Edit --}}
                                    <a href="{{ route('stokbarang.edit', $item->id) }}" 
                                       class="text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-full transition-all duration-200 transform hover:scale-110" 
                                       title="Edit Stok">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if(auth()->user()->role == 'admin')
                                    {{-- Hapus --}}
                                    <button type="button" 
                                            onclick="if(confirm('Hapus riwayat stok masuk ini?')) { document.getElementById('delete-form-{{ $item->id }}').submit(); }"
                                            class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-full transition-all duration-200 transform hover:scale-110"
                                            title="Hapus Stok">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role == 'admin' ? '8' : '7' }}" class="px-5 py-10 border-b border-gray-200 bg-white text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-box-open text-4xl mb-3 text-gray-300"></i>
                                    <p class="text-sm">
                                        @if(request('search'))
                                            Tidak ada hasil untuk pencarian: <b>{{ request('search') }}</b>
                                        @elseif($selectedKategoriId)
                                            Kategori ini belum ada riwayat stok masuk.
                                        @else
                                            Belum ada data stok masuk.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 bg-gray-50 border-t border-gray-200 flex flex-col xs:flex-row items-center xs:justify-between ">
                {{ $stokBarangs->links('pagination.tailwind-custom') }}
            </div>

        </div>
    </form>
    
    {{-- Form-form tersembunyi untuk tombol hapus per item --}}
    @foreach ($stokBarangs as $item)
        @if(auth()->user()->role == 'admin')
            <form id="delete-form-{{ $item->id }}" action="{{ route('stokbarang.destroy', $item->id) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    @endforeach

</div>


{{-- SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const checkAll = document.getElementById('checkAllStock');
    const checkboxes = document.querySelectorAll('.item-stock-checkbox');
    const deleteBtn = document.getElementById('delete-multiple-stock-btn');

    function toggleButton() {
        if(deleteBtn) {
            const checkedCount = document.querySelectorAll('.item-stock-checkbox:checked').length;
            deleteBtn.disabled = checkedCount === 0;
            
            // Efek visual saat disabled/enabled
            if (deleteBtn.disabled) {
                deleteBtn.classList.add('opacity-50', 'cursor-not-allowed', 'transform-none', 'shadow-none');
            } else {
                deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'transform-none', 'shadow-none');
            }
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = checkAll.checked);
            toggleButton();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (!cb.checked && checkAll) checkAll.checked = false;
            // Cek jika semua tercentang manual
            const allChecked = document.querySelectorAll('.item-stock-checkbox:checked').length === checkboxes.length;
            if (checkAll) checkAll.checked = allChecked;
            
            toggleButton();
        });
    });

    toggleButton();
});


function resetSearchAndKeepCategory() {
    const searchInput = document.getElementById('search_input');
    const kategoriId = document.getElementById('kategori_id').value;
    
    searchInput.value = '';
    const params = new URLSearchParams(window.location.search);
    params.delete('search');
    
    if (kategoriId !== "") {
        params.set('kategori_id', kategoriId);
    } else {
        params.delete('kategori_id');
    }

    window.location.href = '{{ route('stokbarang.index') }}?' + params.toString();
}
</script>

@endsection