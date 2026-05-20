<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_booking_rapats', function (Blueprint $table) {
            $table->id();
            
            // Info Pemohon & Ruangan
            $table->string('divisi_pemohon'); // Contoh: Divisi Pemasyarakatan, Divisi Keimigrasian
            $table->string('ruangan'); // Nama ruang rapat
            
            // Detail Rapat
            $table->string('judul_rapat');
            $table->date('tanggal_rapat');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->integer('jumlah_peserta')->default(0);
            
            // Fasilitas (JSON array checkboxes)
            $table->json('fasilitas')->nullable(); 
            
            // Dokumen & Status
            $table->text('keterangan')->nullable();
            $table->string('surat_undangan')->nullable(); // Upload Nota Dinas/Undangan
            $table->string('status')->default('Menunggu Persetujuan'); // Menunggu, Disetujui, Ditolak, Selesai
            $table->text('catatan_admin')->nullable(); // Alasan jika ditolak
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_booking_rapats');
    }
};