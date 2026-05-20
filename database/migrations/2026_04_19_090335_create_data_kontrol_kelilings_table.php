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
        Schema::create('data_kontrol_kelilings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            $table->string('waktu_kontrol')->nullable();
            $table->string('nama_petugas_kontrol');
            $table->string('area_kontrol')->nullable();
            $table->string('hasil_kontrol')->nullable();
            $table->string('dokumentasi_kontrol'); //file
            $table->text('tindak_lanjut')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_kontrol_kelilings');
    }
};