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
        Schema::create('data_bulanan_sdms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            
            $table->integer('jumlah_pegawai')->default(0);
            $table->integer('jumlah_pejabat_struktural')->default(0);
            $table->integer('jumlah_jf')->default(0);
            $table->integer('jumlah_petugas_penjagaan')->default(0);
            $table->integer('jumlah_staff_pembinaan')->default(0);
            $table->integer('jumlah_staff')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_bulanan_sdms');
    }
};