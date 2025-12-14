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
    Schema::create('stok_barangs', function (Blueprint $table) {
        $table->id();
        
        // Kolom penghubung ke tabel 'barangs'
        $table->unsignedBigInteger('id_barang'); 
        
        $table->integer('stok');
        $table->date('tanggal_masuk');
        $table->timestamps();

        // Definisikan foreign key (penghubung)
        $table->foreign('id_barang')->references('id')->on('barangs');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_barangs');
    }
};
