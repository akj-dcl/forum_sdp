<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_pengawalan_pengamanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            $table->date('tanggal_pelaksanaan');
            $table->time('waktu_pelaksanaan_mulai')->nullable();
            $table->time('waktu_pelaksanaan_selesai')->nullable();  
            
            $table->foreignId('jenis_pengawalan_id')->constrained('jenis_pengawalans')->cascadeOnDelete();
            
            // WBP
            $table->integer('jumlah_wbp')->default(0); 
            $table->json('detail_wbp_dikawal')->nullable(); // Diubah jadi JSON
            $table->foreignId('jenis_klasifikasi_id')->constrained('jenis_klasifikasis')->cascadeOnDelete(); // Typo diperbaiki
            
            // Petugas & Surat
            $table->integer('jumlah_petugas')->default(0); 
            $table->json('detail_petugas_pengawalan')->nullable(); // Diubah jadi JSON
            $table->string('surat_perintah')->nullable(); // Nomor Surat Perintah
            
            $table->text('sarana_pengawalan')->nullable();
            
            // File Uploads
            $table->string('laporan_pelaksanaan_pengawalan')->nullable(); // PDF
            $table->string('dokumentasi')->nullable(); // Foto
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_pengawalan_pengamanans');
    }
};