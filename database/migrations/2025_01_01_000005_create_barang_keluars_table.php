<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('id_transaksi')->unique();
            
            $table->unsignedBigInteger('pelanggan_id'); // Penghubung ke 'pelanggans'
            $table->unsignedBigInteger('user_id');    // Penghubung ke 'users'
            
            $table->date('tanggal');
            $table->integer('total_harga')->default(0);
            $table->timestamps();

            // Sambungan ini akan BERHASIL karena 'pelanggans' sudah dibuat
            $table->foreign('pelanggan_id')->references('id')->on('pelanggans');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_keluars');
    }
};