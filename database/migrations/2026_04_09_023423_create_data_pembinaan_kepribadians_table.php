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
        Schema::create('data_pembinaan_kepribadians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->foreignId('jenis_kepribadian_id')->constrained('jenis_kepribadians')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('nama_kegiatan');
            $table->string('pemateri');
            $table->integer('jumlah_peserta');
            $table->text('hasil_kegiatan');
            $table->string('dokumentasi_kegiatan');
            $table->text('rekomendasi_kegiatan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_pembinaan_kepribadians');
    }
};
