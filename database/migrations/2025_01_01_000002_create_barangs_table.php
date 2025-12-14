<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id(); // 'id' (Primary Key)
            
            // Ini adalah 'id_kategori' (Penghubung / Foreign Key)
            $table->unsignedBigInteger('id_kategori'); 
            
            $table->string('kode_barang')->unique(); // 'unique()' agar tidak ada yg sama
            $table->string('nama_barang');
            $table->integer('harga');
            $table->timestamps(); // 'created_at' dan 'updated_at'

            // Memberitahu database bahwa 'id_kategori' terhubung ke 'id' di tabel 'kategoris'
            $table->foreign('id_kategori')->references('id')->on('kategoris');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};