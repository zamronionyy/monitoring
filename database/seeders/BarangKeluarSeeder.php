<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use Illuminate\Support\Str;

class BarangKeluarSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $idTransaksi = 'TRX-' . strtoupper(Str::random(6));
            $idPelanggan = 'PLG-' . strtoupper(Str::random(5));

            $header = BarangKeluar::create([
                'id_transaksi' => $idTransaksi,
                'id_pelanggan' => $idPelanggan,
                'nama_pelanggan' => 'Pelanggan ' . $i,
                'tanggal_keluar' => now()->subDays(rand(1, 10)),
                'penerima' => 'Penerima ' . $i,
                'total_barang' => rand(2, 5),
            ]);

            // Tambahkan detail barang (tiap pelanggan bisa beli banyak)
            for ($j = 1; $j <= $header->total_barang; $j++) {
                $harga = rand(10000, 100000);
                $jumlah = rand(1, 5);
                BarangKeluarDetail::create([
                    'id_transaksi' => $idTransaksi,
                    'kode_barang' => 'KD' . str_pad($j, 3, '0', STR_PAD_LEFT),
                    'nama_barang' => 'Barang ' . $j,
                    'jumlah_keluar' => $jumlah,
                    'harga_satuan' => $harga,
                    'subtotal' => $harga * $jumlah,
                ]);
            }
        }
    }
}
