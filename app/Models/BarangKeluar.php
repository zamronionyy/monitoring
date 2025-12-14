<?php

namespace App\Models; // <-- Pastikan 'A' di 'App' besar

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    use HasFactory;

    // ===========================================
    // === INI ADALAH PERBAIKAN UNTUK ERROR ANDA ===
    // (Daftar izin kolom yang 'rapi')
    // ===========================================
    protected $fillable = [
        'id_transaksi',
        'pelanggan_id', 
        'user_id',      
        'tanggal',
        'total_harga',
        'biaya_kirim',
        'uang_muka',
    ];

    // Relasi: 1 Nota (BarangKeluar) DIMILIKI OLEH 1 Pelanggan
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    // Relasi: 1 Nota (BarangKeluar) DICATAT OLEH 1 User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: 1 Nota (BarangKeluar) punya BANYAK Detail
    public function detailBarangKeluars()
    {
        return $this->hasMany(DetailBarangKeluar::class, 'barang_keluar_id');
    }
}