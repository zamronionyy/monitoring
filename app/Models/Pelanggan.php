<?php

namespace App\Models; // <-- Pastikan 'A' di 'App' besar

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;
    
    protected $table = 'pelanggans';

    protected $fillable = [
        'kode_pelanggan',
        'nama_pelanggan',
        'alamat',
        'no_telp',
    ];

    public function barangKeluars()
    {
        return $this->hasMany(BarangKeluar::class, 'pelanggan_id');
    }
}