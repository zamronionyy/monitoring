<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// BARIS INI DIHAPUS: use Illuminate\Database\Eloquent\SoftDeletes; 

class Barang extends Model
{
    // SoftDeletes dihapus
    use HasFactory; 

    protected $fillable = [
        'id_kategori', 
        'kode_barang', 
        'nama_barang', 
        'harga',
    ];

    /**
     * Relasi ke Kategori
     */ 
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    /**
     * Relasi ke StokBarang (Stok Masuk)
     */ 
    public function stokBarangs()
    {
        // Berdasarkan info yang Anda berikan sebelumnya, nama tabel adalah `stok_barang`.
        return $this->hasMany(StokBarang::class, 'id_barang');
    }

    /**
     * Relasi ke DetailBarangKeluar (Stok Keluar/Penjualan)
     */ 
    public function detailBarangKeluars()
    {
        // Berdasarkan info yang Anda berikan sebelumnya, nama tabel adalah `detail_barang_keluar`.
        return $this->hasMany(DetailBarangKeluar::class, 'barang_id');
    }

    // Ini mungkin tidak diperlukan, cek kembali relasi Anda
    public function barangs() {
    return $this->hasMany(Barang::class, 'kategori_id');
    }
}