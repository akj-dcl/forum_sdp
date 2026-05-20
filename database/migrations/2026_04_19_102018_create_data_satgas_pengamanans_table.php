<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_satgas_pengamanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            
            // 1. KEGIATAN RAZIA/PENGGELEDAHAN KAMAR
            $table->date('tanggal_pelaksanaan_penggeledahan')->nullable();
            $table->time('waktu_pelaksanaan_penggeledahan')->nullable();
            $table->string('lokasi_kamar_penggeledahan')->nullable(); // Typo diperbaiki
            $table->integer('jumlah_anggota_penggeledahan')->default(0);
            $table->text('hasil_penggeledahan')->nullable();
            $table->text('tindak_lanjut_penggeledahan')->nullable();
            
            // 2. KEGIATAN TES NARKOBA
            $table->date('tanggal_pelaksanaan_narkoba')->nullable();
            $table->time('waktu_pelaksanaan_narkoba')->nullable();
            // Kolom lokasi kamar narkoba dihapus karena di PPT tidak ada
            $table->integer('jumlah_yang_dites_narkoba')->default(0);
            $table->text('hasil_tes_narkoba')->nullable();
            $table->text('tindak_lanjut_hasil_tes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_satgas_pengamanans');
    }
};