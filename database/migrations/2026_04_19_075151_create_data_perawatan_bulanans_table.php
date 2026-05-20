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
        Schema::create('data_perawatan_bulanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            
            // Kolom Angka (Jumlah)
            $table->integer('jumlah_penghuni_hiv')->default(0);
            $table->integer('jumlah_penghuni_tbc')->default(0);
            $table->integer('jumlah_penghuni_disabilitas')->default(0);
            $table->integer('jumlah_penghuni_jiwa')->default(0);
            $table->integer('jumlah_penghuni_tidakmenular')->default(0);
            $table->integer('jumlah_penghuni_menular')->default(0);
            $table->string('jenis_penyakit_dominan');
            $table->integer('jumlah_wbp_lansia')->default(0);
            $table->integer('jumlah_wbp_berkepanjangan')->default(0);
            $table->integer('jumlah_peserta_bpjs')->default(0);
            $table->string('laporan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_perawatan_bulanans');
    }
};
