<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_pelanggaran_tatibs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            
            // Sesuai dengan 6 poin di PPT
            $table->string('nama_wbp'); // Poin 1 (Tambahan yang terlewat)
            $table->date('tanggal_pelanggaran')->nullable(); // Poin 2
            $table->time('waktu_pelanggaran')->nullable();   // Poin 2
            $table->string('jenis_pelanggaran')->nullable(); // Poin 3
            $table->string('pasal_pelanggaran')->nullable(); // Poin 4
            $table->text('tindak_lanjut')->nullable();       // Poin 5
            $table->text('rekomendasi_pembinaan')->nullable(); // Poin 6
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_pelanggaran_tatibs');
    }
};