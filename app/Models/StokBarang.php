<?php

namespace App\Models;
use App\Models\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokBarang extends Model
{
    use HasFactory;

    protected $table = 'stok_barangs';

    protected $fillable = [
        'id_barang', 
        'stok', 
        'tanggal_masuk'
    ];

   
    public function barang()
    {
        
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function index()
    {
       
        $barangs = Barang::with('kategori') 
                            ->withSum('stokBarangs', 'stok') 
                            ->paginate(10);

        return view('barang.index', ['barangs' => $barangs]);
    }
}