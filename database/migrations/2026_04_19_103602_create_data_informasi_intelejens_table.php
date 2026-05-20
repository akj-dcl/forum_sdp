<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_informasi_intelejens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            
            $table->date('tanggal_pelaksanaan')->nullable(); // Poin 1 (Waktu)
            $table->time('waktu_pelaksanaan')->nullable();   // Poin 1 (Waktu)
            $table->string('narasumber')->nullable();        // Poin 2 (Tambahan yang terlewat)
            
            $table->text('data_yang_masuk')->nullable();     // Poin 3
            $table->text('data_dukung')->nullable();         // Poin 4
            $table->text('potensi_gangguan')->nullable();    // Poin 5 (Typo diperbaiki)
            $table->text('rekomendasi_antisipasi')->nullable(); // Poin 6
            $table->text('tindak_lanjut_rekomendasi')->nullable(); // Poin 7
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_informasi_intelejens');
    }
};