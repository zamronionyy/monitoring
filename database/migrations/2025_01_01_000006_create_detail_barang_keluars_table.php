<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_barang_keluars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_keluar_id'); // Penghubung ke kepala nota
            $table->unsignedBigInteger('barang_id'); // Penghubung ke tabel barangs
            
            $table->integer('jumlah');
            $table->integer('harga_satuan'); 
            $table->integer('total_harga'); 
            $table->timestamps();

            // Sambungan ini akan BERHASIL karena 'barang_keluars' dan 'barangs' sudah ada
            $table->foreign('barang_keluar_id')->references('id')->on('barang_keluars')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('barangs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_barang_keluars');
    }
};