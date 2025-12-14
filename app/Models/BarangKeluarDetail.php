<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluarDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_transaksi',
        'kode_barang',
        'nama_barang',
        'jumlah_keluar',
        'harga_satuan',
        'subtotal',
    ];
}
