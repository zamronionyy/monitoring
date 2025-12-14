@extends('layouts.app')
@section('title', 'Tambah Penjualan Baru')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translate3d(0, 20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
    .animate-fade-in-up { animation-name: fadeInUp; animation-duration: 0.5s; animation-fill-mode: forwards; }
    
    /* MODIFIKASI SELECT2 UNTUK MEMASTIKAN PUSAT VERTIKAL */
    .select2-container .select2-selection--single { 
        height: 44px !important; 
        border: 1px solid #d1d5db !important; 
        border-radius: 0.5rem !important; 
        padding: 0 0.75rem !important; 
        display: flex !important; 
        align-items: center !important; /* Membuat teks di tengah */
        padding-right: 2.5rem !important; /* Memberi ruang untuk panah */
    }
    
    /* CSS BARU: Mempusatkan Panah Select2 */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important; /* Tinggi sedikit kurang dari container */
        top: 0 !important;
        right: 0 !important;
        width: 30px; /* Lebar area panah */
        display: flex;
        align-items: center; /* Memposisikan ikon panah ke tengah vertikal */
        justify-content: center; /* Memposisikan ikon panah ke tengah horizontal */
    }

    .flatpickr-input[readonly] { 
        background-color: white !important; 
        cursor: pointer; 
        padding-right: 2.5rem !important; 
    }
    
    .flatpickr-calendar-container .flatpickr-input-container {
        display: flex;
        align-items: center;
    }
</style>

@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script src="//unpkg.com/alpinejs" defer></script>

<script>
    $(document).ready(function() { 
        $('#pelanggan_id').select2({ placeholder: '-- Pilih Pelanggan --', width: '100%' });
        flatpickr("#tanggal", { dateFormat: "Y-m-d", altInput: true, altFormat: "l, j F Y", locale: "id", maxDate: "today", allowInput: true });
    });
    document.addEventListener('alpine:init', () => {
        Alpine.directive('init-select2', (el, {}, { cleanup }) => {
            const $el = $(el); $el.select2({ placeholder: '-- Pilih Barang --', width: '100%' }).on('change', (e) => { el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true })); }); cleanup(() => { $el.select2('destroy'); });
        });
    });
    function notaForm() {
        return {
            items: [{ barang_id: '', jumlah: 1, harga_satuan: 0, subtotal: 0 }], biayaKirim: 0, uangMuka: 0,
            get subtotalBarang() { return this.items.reduce((t, i) => t + i.subtotal, 0); }, 
            get grandTotal() { return this.subtotalBarang + this.biayaKirim - this.uangMuka; }, // Total Tagihan = Subtotal + Ongkir - DP
            addItem() { this.items.push({ barang_id: '', jumlah: 1, harga_satuan: 0, subtotal: 0 }); }, removeItem(i) { if(this.items.length > 1) this.items.splice(i, 1); },
            updateHarga(e, i) { const s = e.target.options[e.target.selectedIndex]; this.items[i].harga_satuan = parseFloat(s.getAttribute('data-harga'))||0; this.calculateSubtotal(i); },
            calculateSubtotal(i) { this.items[i].subtotal = this.items[i].harga_satuan * this.items[i].jumlah; },
            formatRupiah(n) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n); }
        }
    }
</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 animate-fade-in-up">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center"><span class="bg-blue-600 text-white p-2 rounded-lg mr-3 shadow-md"><i class="fas fa-cart-plus"></i></span> Tambah Penjualan Baru</h2>
        <a href="{{ route('barangkeluar.index') }}" class="text-gray-500 hover:text-gray-800 transition-colors flex items-center font-medium"><i class="fas fa-arrow-left mr-2"></i> Kembali</a>
    </div>

    <form action="{{ route('barangkeluar.store') }}" method="POST" class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100" x-data="notaForm()">
    @csrf
    <div class="p-6 md:p-8">
        @if ($errors->any()) <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-6"><strong>Oops!</strong><ul class="mt-1 list-disc list-inside text-sm">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div> @endif
        
        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 mb-8">
            <h3 class="text-lg font-semibold text-gray-700 border-b border-gray-300 pb-2 mb-4"><i class="fas fa-file-invoice mr-2 text-gray-500"></i> Informasi Transaksi</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div><label class="block text-sm font-medium text-gray-700">No. Transaksi</label><input type="text" name="id_transaksi" value="{{ old('id_transaksi', 'TRX-' . strtoupper(Str::random(5))) }}" class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3" required></div>
                <div><label class="block text-sm font-medium text-gray-700">Tanggal</label><input type="text" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 bg-white" required placeholder="Pilih Tanggal..."></div>
                <div><label class="block text-sm font-medium text-gray-700">Pelanggan</label><div class="mt-1"><select name="pelanggan_id" id="pelanggan_id" class="w-full" required><option value="">-- Pilih Pelanggan --</option>@foreach ($pelanggans as $p)<option value="{{ $p->id }}">{{ $p->nama_pelanggan }}</option>@endforeach</select></div></div>
            </div>
        </div>

        <div class="space-y-4">
            <template x-for="(item, index) in items" :key="index">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end p-5 border border-gray-200 rounded-xl bg-gray-50">
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barang</label>
                        <select :name="'items[' + index + '][barang_id]'" class="w-full" x-model="item.barang_id" @change="updateHarga($event, index)" x-init-select2 required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach ($barangs as $b)
                                <option value="{{ $b->id }}" data-harga="{{ $b->harga }}">{{ $b->kode_barang }} - {{ $b->nama_barang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Qty</label><input type="number" :name="'items[' + index + '][jumlah]'" class="block w-full rounded-lg border-gray-300 p-2 text-center" x-model.number="item.jumlah" @input="calculateSubtotal(index)" min="1" required></div>
                    <div class="md:col-span-3"><label class="block text-sm font-medium text-gray-700 mb-1">Subtotal</label><input type="text" class="block w-full rounded-lg bg-gray-100 font-bold" :value="formatRupiah(item.subtotal)" disabled readonly></div>
                    <div class="md:col-span-2 flex md:justify-end pb-0.5"><button @click.prevent="removeItem(index)" class="p-2.5 rounded-lg text-white bg-red-500 hover:bg-red-600" :disabled="items.length <= 1"><i class="fas fa-trash-alt"></i></button></div>
                </div>
            </template>
        </div>
        <button @click.prevent="addItem()" class="mt-5 inline-flex items-center bg-green-500 text-white px-5 py-2.5 rounded-lg font-semibold shadow-md hover:bg-green-600"><i class="fas fa-plus-circle mr-2"></i> Tambah Barang</button>

        <div class="mt-8 border-t border-gray-100 pt-6">
            <div class="flex flex-col md:flex-row justify-end gap-6">
                <div class="w-full md:w-1/3 space-y-4">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200"><label class="block text-sm font-bold text-gray-700 mb-1">Biaya Kirim</label><div class="relative"><span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">Rp</span><input type="number" name="biaya_kirim" x-model.number="biayaKirim" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-right font-bold" min="0"></div></div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200"><label class="block text-sm font-bold text-gray-700 mb-1">Uang Muka (DP)</label><div class="relative"><span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">Rp</span><input type="number" name="uang_muka" x-model.number="uangMuka" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-right font-bold" min="0"></div></div>
                </div>
                <div class="w-full md:w-1/3 bg-blue-50 p-5 rounded-xl border border-blue-100 shadow-sm space-y-3">
                    {{-- 1. Subtotal --}}
                    <div class="flex justify-between items-center text-sm border-b border-blue-200 pb-2"><span class="text-gray-600">Subtotal Barang</span><span class="font-bold text-gray-800" x-text="formatRupiah(subtotalBarang)"></span></div>
                    {{-- 2. Ongkir --}}
                    <div class="flex justify-between items-center text-sm border-b border-blue-200 pb-2"><span class="text-gray-600">Biaya Kirim</span><span class="font-bold text-gray-800" x-text="formatRupiah(biayaKirim)"></span></div>
                    {{-- 3. Uang Muka --}}
                    <div class="flex justify-between items-center text-sm border-b border-blue-200 pb-2"><span class="text-gray-600">Uang Muka (DP)</span><span class="font-bold text-red-600" x-text="'- ' + formatRupiah(uangMuka)"></span></div>
                    {{-- 4. Total Tagihan (Grand Total) --}}
                    <div class="flex justify-between items-center pt-1"><span class="text-lg font-extrabold text-gray-700 uppercase">Total Tagihan</span><span class="text-2xl font-extrabold text-blue-700" x-text="formatRupiah(grandTotal)"></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-gray-50 px-6 py-5 border-t border-gray-200 flex justify-end space-x-3 rounded-b-xl">
        <a href="{{ route('barangkeluar.index') }}" class="text-gray-600 hover:text-red-600 px-4 font-medium">Batal</a>
        <button type="submit" class="py-3 px-6 rounded-lg text-white bg-blue-600 hover:bg-blue-700 font-bold shadow-md"><i class="fas fa-save mr-2"></i> Simpan Transaksi</button>
    </div>
    </form>
</div>
@endsection