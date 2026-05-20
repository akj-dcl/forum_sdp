<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_regu_pengamanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            $table->string('shift_rupam')->nullable(); // Pagi, Siang, Malam
            $table->string('nama_regu');
            $table->time('waktu_tugas_mulai'); // Lebih baik pakai tipe time
            $table->time('waktu_tugas_selesai');
            
            // Kehadiran
            $table->integer('jumlah_regu_jaga')->default(0);
            $table->integer('jumlah_hadir')->default(0);
            $table->integer('jumlah_tidak_hadir')->default(0);
            
            // Susunan Petugas
            $table->string('nama_komandan_jaga')->nullable();
            $table->string('nama_wadan_jaga')->nullable();
            $table->string('nama_p2u')->nullable();
            $table->string('nama_petugas_penjagaan')->nullable();
            $table->integer('jumlah_petugas_luar')->default(0);
            
            // Bantuan (Ini yang kita tambahkan agar bisa pilih TNI/Polri)
            $table->string('jenis_bantuan')->nullable(); // TNI, POLRI, atau TNI & POLRI
            $table->integer('jumlah_bantuan')->default(0);
            
            // Pos Pengamanan
            $table->integer('jumlah_titik_pos')->default(0);
            $table->string('pos_pengamanan_diisi')->nullable();
            $table->string('pos_pengamanan_tidak_diisi')->nullable();
            
            $table->string('dokumentasi'); // File PDF
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_regu_pengamanans');
    }
};