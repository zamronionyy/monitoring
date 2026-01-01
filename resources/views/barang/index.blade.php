@extends('layouts.app') 

@section('title', 'Manajemen Barang (Katalog)')

@section('content')

{{-- CSS KHUSUS UNTUK ANIMASI & STYLE --}}
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
    /* Checkbox Custom Cursor */
    input[type="checkbox"] { cursor: pointer; }
</style>

<div class="animate-fade-in-up">

    {{-- HEADER & TOOLS --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
        
        {{-- BAGIAN KIRI: TOMBOL AKSI (Akses: Admin & CEO) --}}
        @if(in_array(auth()->user()->role, ['admin', 'ceo']))
            <form id="delete-multiple-form" action="{{ route('barang.deleteMultiple') }}" method="POST" class="flex flex-wrap items-center gap-2">
                @csrf
                @method('DELETE')
                
                {{-- Tombol Tambah --}}
                <a href="{{ route('barang.create') }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-blue-700 hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Tambah Barang
                </a>

                {{-- Tombol Kategori --}}
                <a href="{{ route('kategori.index') }}" 
                   class="bg-yellow-500 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-yellow-600 hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center">
                    <i class="fas fa-tags mr-2"></i> Kategori
                </a>

                {{-- DROPDOWN IMPORT/EXPORT (Alpine) --}}
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
                            <a href="{{ route('barang.showImportForm') }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <i class="fas fa-upload mr-3 text-gray-400 group-hover:text-blue-500"></i> Import Data
                            </a>
                            <a href="{{ route('barang.exportExcel') }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <i class="fas fa-download mr-3 text-gray-400 group-hover:text-blue-500"></i> Export Data
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Tombol Hapus Massal --}}
                <button type="submit" id="delete-multiple-btn" 
                        class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-red-700 hover:shadow-lg active:scale-95 transform transition-all duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
                        disabled>
                    <i class="fas fa-trash-alt mr-2"></i> Hapus
                </button>
            </form>
        @endif
        
        {{-- BAGIAN KANAN: FILTER & PENCARIAN --}}
        <form action="{{ route('barang.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
            
            {{-- Filter Kategori --}}
            <div x-data="{ 
                    open: false, 
                    selectedId: '{{ $selectedKategoriId }}',
                    selectedName: '{{ $selectedKategoriId ? ($kategoris->firstWhere('id', $selectedKategoriId)->nama_kategori ?? 'Semua Kategori') : 'Semua Kategori' }}',
                    select(id) {
                        document.getElementById('kategori_id_hidden').value = id;
                        document.getElementById('kategori_id_hidden').closest('form').submit();
                    }
                 }" 
                 class="relative min-w-[220px] group"> 
                
                <input type="hidden" name="kategori_id" id="kategori_id_hidden" value="{{ $selectedKategoriId }}">

                <button type="button" @click="open = !open" @click.away="open = false"
                    class="flex items-center justify-between w-full pl-3 pr-3 py-2 bg-white border border-gray-300 
                           rounded-lg shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 
                           transition-all duration-200 h-10">
                    
                    <div class="flex items-center gap-2 text-gray-700">
                        <i class="fas fa-filter text-gray-500"></i>
                        <span class="text-sm font-medium truncate max-w-[150px]" x-text="selectedName"></span>
                    </div>

                    <i class="fas fa-chevron-down text-xs text-gray-700 transition-transform duration-200" 
                       :class="{'rotate-180': open}"></i>
                </button>

                <div x-show="open" 
                     style="display: none;"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden origin-top-left">
                    
                    <div class="max-h-64 overflow-y-auto custom-scrollbar">
                        <div @click="select('')" 
                             class="px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 cursor-pointer transition-colors border-b border-gray-50 flex items-center justify-between">
                            <span>Semua Kategori</span>
                            <template x-if="!selectedId">
                                <i class="fas fa-check text-blue-500 text-xs"></i>
                            </template>
                        </div>

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
            <div class="relative flex items-center w-full sm:max-w-xs group">
                <input type="text" name="search" id="search_input"
                        placeholder="Cari Kode, Nama..." 
                        value="{{ request('search') }}" 
                        autocomplete="off"
                        class="block w-full border border-gray-300 py-2 pl-4 pr-10 
                               rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 outline-none group-hover:shadow-md">
                
                @if(request('search'))
                    <button type="button" onclick="resetSearchAndKeepCategory()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 hover:scale-110 transition-all cursor-pointer">
                        <i class="fas fa-times-circle text-lg"></i>
                    </button>
                @else
                     <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 group-focus-within:text-indigo-500 transition-colors cursor-pointer">
                         <i class="fas fa-search text-lg"></i>
                     </button>
                @endif
            </div>
        </form>
    </div>

    {{-- NOTIFIKASI --}}
    @foreach (['success' => 'green', 'error' => 'red', 'warning' => 'orange'] as $msg => $color)
        @if (session($msg))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-300" 
                 x-transition:leave-start="opacity-100 transform translate-y-0" 
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="bg-{{ $color }}-100 border-l-4 border-{{ $color }}-500 text-{{ $color }}-700 px-4 py-3 rounded shadow-md relative mb-4 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <span>{{ session($msg) }}</span>
            </div>
        @endif
    @endforeach

    {{-- TABEL --}}
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-50 border-b-2 border-gray-200">
                        <th class="px-4 py-3 text-left w-10">
                            <input type="checkbox" id="checkAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-4 w-4">
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Harga Jual</th>
                        <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($barangs as $barang)
                        <tr class="hover:bg-blue-50 transition-colors duration-150 group">
                            
                            {{-- Checkbox --}}
                            <td class="px-4 py-4 bg-white group-hover:bg-blue-50 text-sm">
                                <input type="checkbox" data-id="{{ $barang->id }}" class="item-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-4 w-4">
                            </td>

                            {{-- Kode --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm">
                                <span class="font-mono font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded text-xs">
                                    {{ $barang->kode_barang }}
                                </span>
                            </td>

                            {{-- Nama --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm font-semibold text-gray-700">
                                {{ $barang->nama_barang }}
                            </td>

                            {{-- Kategori --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm text-gray-600">
                                @if($barang->kategori)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $barang->kategori->nama_kategori }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">Tanpa Kategori</span>
                                @endif
                            </td>

                            {{-- Harga --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-sm text-gray-700">
                              &nbsp;Rp&nbsp;{{ number_format($barang->harga, 0, ',', '.') }}
                            </td>
                            
                            {{-- Stok --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-center">
                                @php
                                    $totalMasuk = (int) $barang->stok_barangs_sum_stok;
                                    $totalKeluar = (int) $barang->detail_barang_keluars_sum_jumlah;
                                    $stokAkhir = $totalMasuk - $totalKeluar;
                                    $stokMinimum = $barang->stok_minimum ?? 0;
                                    
                                    if($stokAkhir <= 0) {
                                        $badgeClass = 'bg-red-100 text-red-800 border-red-200';
                                    } elseif($stokAkhir < $stokMinimum) {
                                        $badgeClass = 'bg-orange-100 text-orange-800 border-orange-200';
                                    } else {
                                        $badgeClass = 'bg-green-100 text-green-800 border-green-200';
                                    }
                                @endphp
                                
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full border {{ $badgeClass }}">
                                    {{ $stokAkhir }}
                                </span>
                            </td>

                            {{-- Aksi (Akses: Admin & CEO) --}}
                            <td class="px-5 py-4 bg-white group-hover:bg-blue-50 text-center text-sm whitespace-nowrap">
                                @if(in_array(auth()->user()->role, ['admin', 'ceo']))
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('barang.edit', $barang->id) }}" 
                                           class="text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-full transition-all duration-200 transform hover:scale-110" 
                                           title="Edit Barang">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        {{-- Hapus Satuan --}}
                                        <form id="delete-form-{{ $barang->id }}" action="{{ route('barang.destroy', $barang->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button" onclick="confirmDeleteSingle('{{ $barang->id }}', '{{ addslashes($barang->nama_barang) }}')"
                                                    class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-full transition-all duration-200 transform hover:scale-110"
                                                    title="Hapus Barang">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 border-b border-gray-200 bg-white text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-box-open text-4xl mb-3 text-gray-300"></i>
                                    <p class="text-sm">
                                        @if(request('search'))
                                            Tidak ada barang yang cocok dengan pencarian "<span class="font-bold">{{ request('search') }}</span>".
                                        @elseif(request('kategori_id'))
                                            Kategori ini masih kosong.
                                        @else
                                            Belum ada data barang di katalog.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse 
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 bg-gray-50 border-t border-gray-200 flex flex-col xs:flex-row items-center xs:justify-between">
            {{ $barangs->links('pagination.tailwind-custom') }}
        </div>
    </div>
</div>

{{-- SCRIPT SWEETALERT2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDeleteSingle(id, namaBarang) {
    Swal.fire({
        title: 'Hapus Barang?',
        text: "Barang '" + namaBarang + "' akan dihapus permanen! Stok dan riwayat terkait mungkin akan terpengaruh.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    })
}

document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const deleteBtn = document.getElementById('delete-multiple-btn');
    const formMassal = document.getElementById('delete-multiple-form');

    function collectCheckedIds() {
        const checkedIds = [];
        checkboxes.forEach(cb => {
            if (cb.checked) checkedIds.push(cb.getAttribute('data-id'));
        });
        return checkedIds;
    }

    function toggleDeleteButton() {
        if (!deleteBtn) return;
        deleteBtn.disabled = collectCheckedIds().length === 0;
        if(deleteBtn.disabled){
            deleteBtn.classList.add('opacity-50', 'cursor-not-allowed', 'transform-none');
        } else {
            deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'transform-none');
        }
    }

    if(checkAll){
        checkAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = checkAll.checked);
            toggleDeleteButton();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            if (!this.checked && checkAll) checkAll.checked = false;
            const allChecked = document.querySelectorAll('.item-checkbox:checked').length === checkboxes.length;
            if(checkAll) checkAll.checked = allChecked;
            toggleDeleteButton();
        });
    });

    if(formMassal){
        formMassal.addEventListener('submit', function (e) {
            e.preventDefault();

            const ids = collectCheckedIds();
            if (ids.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Tidak ada barang yang dipilih!',
                });
                return;
            }

            Swal.fire({
                title: 'Hapus ' + ids.length + ' Barang?',
                text: "Data yang dipilih akan dihapus permanen! Tindakan ini tidak bisa dibatalkan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

                    ids.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        this.appendChild(input);
                    });

                    this.submit();
                }
            });
        });
    }

    if(deleteBtn) toggleDeleteButton();
});


/// FIX → Perbaikan Filter Pencarian
function resetSearchAndKeepCategory() {
    const searchInput = document.getElementById('search_input');

    // FIX: Ganti ke kategori_id_hidden
    const kategoriId = document.getElementById('kategori_id_hidden').value;

    searchInput.value = '';

    const params = new URLSearchParams(window.location.search);

    params.delete('search');

    if (kategoriId !== "") {
        params.set('kategori_id', kategoriId);
    } else {
        params.delete('kategori_id');
    }

    window.location.href = '{{ route('barang.index') }}?' + params.toString();
}
</script>

@endsection