<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_harian_sdms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            
            $table->integer('jumlah_pegawai_keseluruhan')->default(0);
            
            // JSON untuk menampung: hadir, cuti, sakit, ijin
            $table->json('kehadiran_pegawai')->nullable();
            
            // Typo diperbaiki & dibuat nullable
            $table->text('pelanggaran_sdm')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_harian_sdms');
    }
};