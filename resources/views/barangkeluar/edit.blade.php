@extends('layouts.app')

@section('title', 'Edit Penjualan')

<link
    href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
    rel="stylesheet"
/>
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"
>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate3d(0, 20px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    .animate-fade-in-up {
        animation-name: fadeInUp;
        animation-duration: 0.5s;
        animation-fill-mode: forwards;
    }

    .select2-container .select2-selection--single {
        height: 44px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        padding: 0 0.75rem !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        top: 0 !important;
        right: 10px !important;
    }

    .bg-locked {
        background-color: #f3f4f6 !important;
        cursor: not-allowed;
    }
</style>

@section('content')
<div class="animate-fade-in-up">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
            <span class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3 shadow-sm">
                <i class="fas fa-edit"></i>
            </span>
            Edit Nota Penjualan
        </h2>
        <a
            href="{{ route('barangkeluar.index') }}"
            class="text-gray-500 hover:text-gray-700 transition-colors"
        >
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <form
        action="{{ route('barangkeluar.update', $barangKeluar->id) }}"
        method="POST"
        class="bg-white shadow-lg rounded-xl p-8 border border-gray-100"
        x-data='notaForm(@json($itemsForAlpine), {{ $barangKeluar->uang_muka ?? 0 }}, {{ $barangKeluar->biaya_kirim ?? 0 }})'
    >
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-6">
                <strong>Oops!</strong>
                <ul class="mt-1 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 mb-8">
            <h3 class="text-lg font-semibold text-gray-700 border-b border-gray-300 pb-2 mb-4">
                <i class="fas fa-file-invoice mr-2 text-gray-500"></i>
                Informasi Transaksi
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        No. Transaksi
                    </label>
                    <input
                        type="text"
                        name="id_transaksi"
                        value="{{ old('id_transaksi', $barangKeluar->id_transaksi) }}"
                        class="w-full pl-3 border border-gray-300 rounded-lg py-2.5"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Tanggal (Terkunci)
                    </label>
                    <input
                        type="text"
                        name="tanggal"
                        id="tanggal"
                        value="{{ old('tanggal', \Carbon\Carbon::parse($barangKeluar->tanggal)->format('Y-m-d')) }}"
                        class="w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-locked"
                        readonly
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Pelanggan
                    </label>
                    <div class="mt-1">
                        <select
                            name="pelanggan_id"
                            id="pelanggan_id"
                            class="w-full"
                            required
                        >
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach ($pelanggans as $p)
                                <option
                                    value="{{ $p->id }}"
                                    {{ old('pelanggan_id', $barangKeluar->pelanggan_id) == $p->id ? 'selected' : '' }}
                                >
                                    {{ $p->nama_pelanggan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <template x-for="(item, index) in items" :key="index">
                <div class="flex flex-col md:flex-row items-end gap-4 p-5 border border-gray-200 rounded-xl bg-gray-50">
                    <div class="w-full md:w-1/2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Barang
                        </label>
                        <select
                            :name="'items[' + index + '][barang_id]'"
                            class="w-full"
                            x-model="item.barang_id"
                            @change="updateHarga($event, index)"
                            x-init-select2
                            required
                        >
                            <option value="">-- Pilih Barang --</option>
                            @foreach ($barangs as $b)
                                @php
                                    $stokTersedia = ($b->stokBarangs->sum('stok') ?? 0) - ($b->detailBarangKeluars->sum('jumlah') ?? 0);
                                @endphp
                                <option
                                    value="{{ $b->id }}"
                                    data-harga="{{ $b->harga }}"
                                    {{-- Simpan stok tersedia di atribut data --}}
                                    data-stok="{{ $stokTersedia }}"
                                    {{ $stokTersedia <= 0 ? 'disabled' : '' }}
                                >
                                     {{ $b->kode_barang }} - {{ $b->nama_barang }} (Stok: {{ $stokTersedia }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-1/6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Qty
                        </label>
                        {{-- PERBAIKAN: Tambahkan atribut :max agar tidak bisa input melebihi stok --}}
                        <input
                            type="number"
                            min="1"
                            :max="item.stok_tersedia"
                            :name="'items[' + index + '][jumlah]'"
                            class="w-full border border-gray-300 rounded-lg p-2.5 text-center"
                            x-model.number="item.jumlah"
                            @input="calculateSubtotal(index)"
                            required
                        >
                    </div>

                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Subtotal
                        </label>
                        <input
                            type="text"
                            class="w-full border border-gray-200 rounded-lg p-2.5 bg-gray-100 font-bold"
                            :value="formatRupiah(item.subtotal)"
                            readonly
                        >
                    </div>

                    <div class="w-full md:w-auto pb-1">
                        <button
                            @click.prevent="removeItem(index)"
                            class="w-full md:w-auto p-2.5 rounded-lg text-white bg-red-500 hover:bg-red-600"
                            :disabled="items.length <= 1"
                        >
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <button
            @click.prevent="addItem()"
            class="mt-5 flex items-center gap-2 bg-green-500 text-white px-5 py-2.5 rounded-lg font-semibold shadow-md hover:bg-green-600"
        >
            <i class="fas fa-plus-circle"></i> Tambah Barang
        </button>

        <div class="mt-8 border-t border-gray-100 pt-6">
            <div class="flex flex-col md:flex-row justify-end gap-6">
                <div class="w-full md:w-1/3 space-y-4">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            Biaya Kirim
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">
                                Rp
                            </span>
                            <input
                                type="number"
                                name="biaya_kirim"
                                x-model.number="biayaKirim"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-right font-bold"
                                min="0"
                            >
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            Uang Muka (DP)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">
                                Rp
                            </span>
                            <input
                                type="number"
                                name="uang_muka"
                                x-model.number="uangMuka"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-right font-bold"
                                min="0"
                            >
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-1/3 bg-blue-50 p-5 rounded-xl border border-blue-100 shadow-sm space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Subtotal Barang</span>
                        <span class="font-bold text-gray-800" x-text="formatRupiah(subtotalBarang)"></span>
                    </div>

                    <div class="flex justify-between items-center text-sm border-b border-blue-200 pb-2">
                        <span class="text-gray-600">Biaya Kirim</span>
                        <span class="font-bold text-gray-800" x-text="formatRupiah(biayaKirim)"></span>
                    </div>

                    <div class="flex justify-between items-center text-sm border-b border-blue-200 pb-2">
                        <span class="text-gray-600">Dikurangi DP</span>
                        <span class="font-bold text-red-600" x-text="'- ' + formatRupiah(uangMuka)"></span>
                    </div>

                    <div class="flex justify-between items-center pt-1">
                        <span class="text-lg font-extrabold text-gray-700 uppercase">
                            Total Tagihan
                        </span>
                        <span class="text-2xl font-extrabold text-blue-700" x-text="formatRupiah(grandTotal)"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 flex justify-between items-center">
            <a
                href="{{ route('barangkeluar.index') }}"
                class="text-gray-500 hover:text-red-500 hover:underline px-4 transition-colors font-medium"
            >
                Batal
            </a>
            <button
                type="submit"
                class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold shadow-md hover:bg-indigo-700"
            >
                <i class="fas fa-save mr-2"></i> Update Transaksi
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script src="//unpkg.com/alpinejs" defer></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.directive('init-select2', (el, {}, { cleanup }) => {
            const $el = $(el);

            $el.select2({
                placeholder: '-- Pilih Barang --',
                width: '100%'
            }).on('change', () => {
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            });

            cleanup(() => {
                $el.select2('destroy');
            });
        });
    });

    $(document).ready(function () {
        $('#pelanggan_id').select2({
            placeholder: '-- Pilih Pelanggan --',
            width: '100%'
        });

        flatpickr('#tanggal', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'l, j F Y',
            locale: 'id',
            clickOpens: false
        });
    });

    function notaForm(initialItems, initialDp = 0, initialOngkir = 0) {
        let startItems = [
            { barang_id: '', jumlah: 1, harga_satuan: 0, subtotal: 0, stok_tersedia: 9999 }
        ];

        if (Array.isArray(initialItems) && initialItems.length > 0) {
            startItems = initialItems.map(item => ({
                ...item,
                subtotal: parseFloat(item.subtotal) || 0,
                harga_satuan: parseFloat(item.harga_satuan) || 0,
                jumlah: parseInt(item.jumlah) || 1,
                // Secara default untuk data yang sudah ada, kita anggap stok mencukupi
                stok_tersedia: 9999 
            }));
        }

        return {
            items: startItems,
            uangMuka: parseFloat(initialDp) || 0,
            biayaKirim: parseFloat(initialOngkir) || 0,

            get subtotalBarang() {
                const total = this.items.reduce(
                    (t, i) => t + (parseFloat(i.subtotal) || 0),
                    0
                );
                return isNaN(total) ? 0 : total;
            },

            get grandTotal() {
                const total =
                    (this.subtotalBarang || 0) +
                    (this.biayaKirim || 0) -
                    (this.uangMuka || 0);

                return isNaN(total) ? 0 : total;
            },

            addItem() {
                this.items.push({
                    barang_id: '',
                    jumlah: 1,
                    harga_satuan: 0,
                    subtotal: 0,
                    stok_tersedia: 9999
                });
            },

            removeItem(i) {
                if (this.items.length > 1) {
                    this.items.splice(i, 1);
                }
            },

            updateHarga(e, i) {
                const s = e.target.options[e.target.selectedIndex];
                this.items[i].harga_satuan = parseFloat(s.getAttribute('data-harga')) || 0;
                // PERBAIKAN: Ambil nilai stok dari atribut data-stok
                this.items[i].stok_tersedia = parseInt(s.getAttribute('data-stok')) || 0;
                
                // Pastikan jumlah tidak melebihi stok yang baru dipilih
                if (this.items[i].jumlah > this.items[i].stok_tersedia) {
                    this.items[i].jumlah = this.items[i].stok_tersedia;
                }
                
                this.calculateSubtotal(i);
            },

            calculateSubtotal(i) {
                // PERBAIKAN: Validasi real-time agar jumlah tidak melebihi stok
                if (this.items[i].jumlah > this.items[i].stok_tersedia) {
                    this.items[i].jumlah = this.items[i].stok_tersedia;
                }
                
                const jumlah = parseInt(this.items[i].jumlah) || 0;
                this.items[i].subtotal =
                    (this.items[i].harga_satuan || 0) * jumlah;
            },

            formatRupiah(n) {
                const number = parseFloat(n) || 0;
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            }
        }
    }
</script>
@endpush
@endsection