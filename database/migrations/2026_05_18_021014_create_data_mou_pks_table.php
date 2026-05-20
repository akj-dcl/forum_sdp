<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_mou_pks', function (Blueprint $table) {
            $table->id();
            // upt_id bisa null jika MOU ini milik Kanwil/Ditjenpas
            $table->foreignId('upt_id')->nullable()->constrained('upts')->cascadeOnDelete();
            
            // Kategori: 'UPT' (pakai tahapan) atau 'KANWIL' / 'DITJENPAS' (langsung referensi)
            $table->string('kategori_pemrakarsa')->default('UPT'); 
            
            $table->string('judul_kerjasama');
            $table->string('jenis_kerjasama');
            $table->string('instansi_kerjasama');
            
            // Status & Log Riwayat Maju-Mundur
            $table->string('status_tahapan')->default('Draft Awal (UPT)'); 
            $table->json('riwayat_tahapan')->nullable(); // Menyimpan log waktu, file, catatan
            
            // Data Final (Diisi saat status sudah 'Selesai')
            $table->date('masa_berlaku_mulai')->nullable();
            $table->date('masa_berlaku_selesai')->nullable();
            $table->string('file_mou_pks')->nullable(); // PDF Final
            $table->string('dokumentasi_penandatanganan')->nullable(); // Foto/PDF
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_mou_pks');
    }
};