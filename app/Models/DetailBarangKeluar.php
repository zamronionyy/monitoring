<?php

namespace App\Models; // <-- Pastikan 'A' di 'App' besar

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailBarangKeluar extends Model
{
    use HasFactory;

    // ===========================================
    // === INI ADALAH VERSI YANG RAPI ===
    // ===========================================
    protected $fillable = [
        'barang_keluar_id',
        'barang_id', // <-- HARUS 'barang_id' (angka)
        'jumlah',
        'harga_satuan',
        'total_harga',
    ];

    // Relasi: 1 Detail DIMILIKI OLEH 1 Barang (Master Produk)
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    // Relasi: 1 Detail DIMILIKI OLEH 1 Nota (Kepala Nota)
    public function barangKeluar()
    {
        return $this->belongsTo(BarangKeluar::class, 'barang_keluar_id');
    }
    
}