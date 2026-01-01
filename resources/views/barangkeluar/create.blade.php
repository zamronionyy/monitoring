@extends('layouts.app')
@section('title', 'Tambah Penjualan Baru')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translate3d(0, 20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
    .animate-fade-in-up { animation-name: fadeInUp; animation-duration: 0.5s; animation-fill-mode: forwards; }
    
    .select2-container .select2-selection--single { 
        height: 44px !important; 
        border: 1px solid #d1d5db !important; 
        border-radius: 0.5rem !important; 
        padding: 0 0.75rem !important; 
        display: flex !important; 
        align-items: center !important; 
    }
    
    .bg-locked { background-color: #f3f4f6 !important; cursor: not-allowed; }
    .input-error { border-color: #ef4444 !important; background-color: #fef2f2 !important; }
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
        
        flatpickr("#tanggal", { 
            dateFormat: "Y-m-d", 
            altInput: true, 
            altFormat: "l, j F Y", 
            locale: "id", 
            defaultDate: "today",
            clickOpens: false 
        });
    });

    document.addEventListener('alpine:init', () => {
        Alpine.directive('init-select2', (el, {}, { cleanup }) => {
            const $el = $(el); 
            $el.select2({ placeholder: '-- Pilih Barang --', width: '100%' })
               .on('change', (e) => { 
                   el.dispatchEvent(new Event('input', { bubbles: true })); 
                   el.dispatchEvent(new Event('change', { bubbles: true })); 
               }); 
            setTimeout(() => { $el.val($el.val()).trigger('change'); }, 50);
            cleanup(() => { $el.select2('destroy'); });
        });
    });

    function notaForm() {
        var oldItems = @json(old('items', []));
        var validationErrors = @json($errors->toArray());
        var defaultItem = [{ barang_id: '', jumlah: 1, harga_satuan: 0, subtotal: 0, stok_maks: 0 }];
        var itemsData = Array.isArray(oldItems) ? oldItems : Object.values(oldItems);
        
        if (itemsData.length === 0) { 
            itemsData = defaultItem; 
        } else {
            itemsData = itemsData.map(item => ({
                ...item,
                stok_maks: parseInt(item.stok_maks) || 0
            }));
        }

        return {
            items: itemsData, 
            biayaKirim: {{ old('biaya_kirim', 0) }}, 
            uangMuka: {{ old('uang_muka', 0) }},
            errors: validationErrors,

            get subtotalBarang() { return this.items.reduce((t, i) => t + (parseFloat(i.subtotal) || 0), 0); }, 
            get grandTotal() { return this.subtotalBarang + (parseInt(this.biayaKirim) || 0) - (parseInt(this.uangMuka) || 0); }, 
            
            addItem() { this.items.push({ barang_id: '', jumlah: 1, harga_satuan: 0, subtotal: 0, stok_maks: 0 }); }, 
            removeItem(i) { if(this.items.length > 1) this.items.splice(i, 1); },
            
            updateHarga(e, i) { 
                const s = e.target.options[e.target.selectedIndex]; 
                if(s && s.getAttribute('data-harga')) {
                    this.items[i].harga_satuan = parseFloat(s.getAttribute('data-harga')) || 0; 
                    this.items[i].stok_maks = parseInt(s.getAttribute('data-stok')) || 0;
                    if(this.items[i].jumlah > this.items[i].stok_maks) {
                        this.items[i].jumlah = this.items[i].stok_maks;
                    }
                    this.calculateSubtotal(i);
                }
            },
            
            calculateSubtotal(i) { 
                if(this.items[i].jumlah > this.items[i].stok_maks) {
                    this.items[i].jumlah = this.items[i].stok_maks;
                }
                this.items[i].subtotal = (this.items[i].harga_satuan || 0) * (this.items[i].jumlah || 0); 
            },
            
            formatRupiah(n) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n || 0); }
        }
    }
</script>

<div class="max-w-7xl mx-auto px-4 py-4 animate-fade-in-up">
    <form action="{{ route('barangkeluar.store') }}" method="POST" class="bg-white shadow-lg rounded-xl border border-gray-100" x-data="notaForm()">
    @csrf
    <div class="p-6 md:p-8">
        {{-- Section 1: Informasi Transaksi --}}
        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">No. Transaksi</label>
                    <input type="text" name="id_transaksi" value="{{ old('id_transaksi', 'TRX-' . strtoupper(Str::random(5))) }}" class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 font-mono bg-white" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                    <input type="text" name="tanggal" id="tanggal" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 bg-locked" readonly required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pelanggan</label>
                    <select name="pelanggan_id" id="pelanggan_id" class="w-full" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach ($pelanggans as $p)
                            <option value="{{ $p->id }}" {{ old('pelanggan_id') == $p->id ? 'selected' : '' }}>{{ $p->nama_pelanggan }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Section 2: Input Barang --}}
        <div class="space-y-4">
            <template x-for="(item, index) in items" :key="index">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end p-5 border rounded-xl bg-gray-50" :class="item.jumlah > 0 && item.jumlah == item.stok_maks ? 'border-orange-300' : 'border-gray-200'">
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barang <span class="text-red-500">*</span></label>
                        <select :name="'items[' + index + '][barang_id]'" class="w-full" x-model="item.barang_id" @change="updateHarga($event, index)" x-init-select2 required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach ($barangs as $b)
                                <option value="{{ $b->id }}" data-harga="{{ $b->harga }}" data-stok="{{ $b->stok_akhir }}" {{ $b->stok_akhir <= 0 ? 'disabled' : '' }}>
                                    {{ $b->kode_barang }} - {{ $b->nama_barang }} (Stok: {{ $b->stok_akhir }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Qty (Maks: <span x-text="item.stok_maks"></span>)</label>
                        <input type="number" :name="'items[' + index + '][jumlah]'" 
                               class="block w-full rounded-lg p-2 border-gray-300 text-center" 
                               :class="item.jumlah > item.stok_maks ? 'input-error' : ''"
                               x-model.number="item.jumlah" 
                               @input="calculateSubtotal(index)" 
                               :max="item.stok_maks" 
                               min="1" required>
                        <input type="hidden" :name="'items[' + index + '][stok_maks]'" x-model="item.stok_maks">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal</label>
                        <input type="text" class="block w-full rounded-lg bg-gray-100 font-bold" :value="formatRupiah(item.subtotal)" readonly>
                        <input type="hidden" :name="'items[' + index + '][harga_satuan]'" :value="item.harga_satuan">
                        <input type="hidden" :name="'items[' + index + '][subtotal]'" :value="item.subtotal">
                    </div>
                    <div class="md:col-span-2 flex md:justify-end">
                        <button @click.prevent="removeItem(index)" class="p-2.5 rounded-lg text-white bg-red-500 hover:bg-red-600 shadow-sm" :disabled="items.length <= 1">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>
        
        <button @click.prevent="addItem()" class="mt-5 bg-green-500 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-green-600 transition-colors">
            <i class="fas fa-plus-circle mr-2"></i> Tambah Barang
        </button>

        {{-- Section 3: Ringkasan Biaya --}}
        <div class="mt-8 border-t border-gray-100 pt-6">
            <div class="flex flex-col md:flex-row justify-end gap-6">
                <div class="w-full md:w-1/3 space-y-4">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Biaya Kirim</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">Rp</span>
                            <input type="number" name="biaya_kirim" x-model.number="biayaKirim" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-right font-bold bg-white" min="0">
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Uang Muka (DP)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">Rp</span>
                            <input type="number" name="uang_muka" x-model.number="uangMuka" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-right font-bold bg-white" min="0">
                        </div>
                    </div>
                    <input type="hidden" name="total_harga" :value="grandTotal">
                </div>
                <div class="w-full md:w-1/3 bg-blue-50 p-5 rounded-xl border border-blue-100 shadow-sm space-y-3">
                    <div class="flex justify-between items-center text-sm border-b border-blue-200 pb-2">
                        <span class="text-gray-600">Subtotal Barang</span>
                        <span class="font-bold text-gray-800" x-text="formatRupiah(subtotalBarang)"></span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-blue-200 pb-2">
                        <span class="text-gray-600">Biaya Kirim</span>
                        <span class="font-bold text-gray-800" x-text="formatRupiah(biayaKirim)"></span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-blue-200 pb-2">
                        <span class="text-gray-600">Uang Muka (DP)</span>
                        <span class="font-bold text-red-600" x-text="'- ' + formatRupiah(uangMuka)"></span>
                    </div>
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-lg font-extrabold text-gray-700 uppercase">Total Tagihan</span>
                        <span class="text-2xl font-extrabold text-blue-700" x-text="formatRupiah(grandTotal)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-gray-50 px-6 py-5 border-t border-gray-200 flex justify-end space-x-3 rounded-b-xl">
        <a href="{{ route('barangkeluar.index') }}" class="px-6 py-3 rounded-lg text-gray-500 font-bold hover:bg-gray-200 transition-all">Batal</a>
        <button type="submit" class="py-3 px-6 rounded-lg text-white bg-blue-600 hover:bg-blue-700 font-bold shadow-md transition-all"><i class="fas fa-save mr-2"></i> Simpan Transaksi</button>
    </div>
    </form>
</div>
@endsection