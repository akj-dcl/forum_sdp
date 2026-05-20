<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_registrasis', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            
            // Kolom JSON Lama
            $table->json('rekap_umum')->nullable();
            $table->json('rekap_pidsus')->nullable();
            $table->json('rekap_pidum')->nullable();
            $table->json('rekap_overstaying')->nullable();
            $table->json('rekap_integrasi')->nullable();
            $table->json('rekap_identitas')->nullable();
            $table->json('rekap_agama')->nullable();
            $table->json('rekap_pengeluaran')->nullable();

            // 9 Kolom JSON Tambahan Baru
            $table->json('rekap_pendidikan')->nullable();
            $table->json('rekap_detail_napi')->nullable();
            $table->json('rekap_detail_tahanan')->nullable();
            $table->json('rekap_petugas')->nullable();
            $table->json('rekap_pengunjung')->nullable();
            $table->json('rekap_wbp_dikunjungi')->nullable();
            $table->json('rekap_wbp_vidcall')->nullable();
            $table->json('rekap_wbp_wartel')->nullable();
            $table->json('rekap_barang_titipan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_registrasis');
    }
};