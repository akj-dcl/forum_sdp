<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_realisasi_anggarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            
            // WAJIB bigInteger untuk uang
            $table->bigInteger('belanja_pegawai_pagu')->default(0);
            $table->bigInteger('belanja_pegawai_realisasi')->default(0); // Diubah jadi realisasi
            
            $table->bigInteger('belanja_barang_pagu')->default(0);
            $table->bigInteger('belanja_barang_realisasi')->default(0);
            
            $table->bigInteger('belanja_modal_pagu')->default(0);
            $table->bigInteger('belanja_modal_realisasi')->default(0);
            
            $table->bigInteger('belanja_listrik_pagu')->default(0);
            $table->bigInteger('belanja_listrik_realisasi')->default(0);
            
            $table->bigInteger('belanja_telpon_pagu')->default(0);
            $table->bigInteger('belanja_telpon_realisasi')->default(0);
            
            $table->bigInteger('belanja_internet_pagu')->default(0);
            $table->bigInteger('belanja_internet_realisasi')->default(0);
            
            $table->bigInteger('belanja_pos_pagu')->default(0);
            $table->bigInteger('belanja_pos_realisasi')->default(0);
            
            // BAMA (Bahan Makanan)
            $table->string('bama_nomor_kontrak')->nullable(); // Typo diperbaiki, ganti string
            $table->bigInteger('bama_pagu_anggaran')->default(0);
            $table->bigInteger('bama_realisasi_anggaran')->default(0);
            $table->string('bama_nama_penyedia')->nullable();
            
            // BAST sesuai PPT
            $table->string('bama_nomor_bast')->nullable();
            $table->date('bama_tanggal_bast')->nullable();
            $table->string('bama_berita_acara')->nullable(); // Typo nullable diperbaiki (File PDF)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_realisasi_anggarans');
    }
};