<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // <-- WAJIB: Untuk DB::raw
use App\Models\DetailBarangKeluar; // <-- WAJIB: Untuk query sub-join

class Kategori extends Model
{
    use HasFactory;
    
    protected $table = 'kategoris';

    protected $fillable = ['nama_kategori'];

    public function barangs()
    {
        return $this->hasMany(Barang::class, 'id_kategori');
    }

    /**
     * Metode untuk mengambil data Kinerja Penjualan (Kuantitas & Omset) per Kategori.
     * Logika ini dipindahkan dari Controller (Langkah 2 & 3).
     */
    public static function getSalesPerformance($tanggalMulai, $tanggalSelesai)
    {
        // 1. Sub-Query untuk menghitung total kuantitas dan total omset per ID Kategori
        $subQueryPenjualan = DetailBarangKeluar::select(
             DB::raw('SUM(detail_barang_keluars.jumlah) AS total_kuantitas'),
             DB::raw('SUM(detail_barang_keluars.total_harga) AS total_omset'),
             'barangs.id_kategori'
            )
            ->join('barangs', 'detail_barang_keluars.barang_id', '=', 'barangs.id')
            ->whereNotNull('barangs.id_kategori')
            ->whereHas('barangKeluar', function ($q) use ($tanggalMulai, $tanggalSelesai) {
                 // Filter berdasarkan kolom 'tanggal' di tabel barang_keluars
                 $q->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]); 
            })
            ->groupBy('barangs.id_kategori');

        // 2. Gabungkan hasil sub-query dengan tabel kategori master
        return self::query()
            ->joinSub($subQueryPenjualan, 'penjualan', function ($join) {
                $join->on('kategoris.id', '=', 'penjualan.id_kategori');
            })
            // Pilih semua kolom kategori dan hasil agregasi
            ->selectRaw('kategoris.*, penjualan.total_kuantitas, penjualan.total_omset')
            ->get();
    }
}