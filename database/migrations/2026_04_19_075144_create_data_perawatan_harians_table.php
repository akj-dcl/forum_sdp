<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_perawatan_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            
            // Kolom Angka (Jumlah)
            $table->integer('jumlah_penghuni_berobat')->default(0);
            $table->integer('jumlah_penghuni_dirawat')->default(0);
            $table->integer('jumlah_wbp_meninggal')->default(0);
            $table->integer('jumlah_nama_berobatjalan')->default(0);
            $table->integer('jumlah_nama_rawatklinik')->default(0);
            
            // Kolom JSON untuk menyimpan daftar Teks Nama WBP
            $table->json('daftar_nama_berobatjalan')->nullable();
            $table->json('daftar_nama_rawatklinik')->nullable();
            
            $table->string('dokumen'); // File PDF
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_perawatan_harians');
    }
};