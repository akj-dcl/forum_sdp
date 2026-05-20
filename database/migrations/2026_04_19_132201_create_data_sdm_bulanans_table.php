<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('data_sdm_bulanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            
            $table->json('jumlah_pegawai_bypendidikan')->nullable();
            $table->json('jumlah_pegawai_byusulan')->nullable();
            $table->json('jumlah_pejabat_struktural')->nullable();
            $table->json('jumlah_pegawai_bygolongan')->nullable();
            $table->integer('jumlah_pegawai_laki')->default(0);
            $table->integer('jumlah_pegawai_perempuan')->default(0);
            $table->json('jumlah_pegawai_bybeban')->nullable();
            $table->integer('jumlah_jf')->default(0);
            $table->json('jumlah_petugas_penjagaan')->nullable();
            $table->integer('jumlah_staff_pembinaan')->default(0);
            $table->integer('jumlah_staff_pengamanan')->default(0);
            $table->integer('jumlah_staff_tudanumum')->default(0);
            $table->integer('jumlah_staff_bimker')->default(0);
            $table->integer('jumlah_pegawai_diktek')->default(0);
            $table->integer('jumlah_pegawai_dikman')->default(0);
            $table->integer('jumlah_pegawai_hukdis')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_sdm_bulanans');
    }
};
