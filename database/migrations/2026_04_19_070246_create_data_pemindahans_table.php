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
        Schema::create('data_pemindahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            $table->date('tanggal_pelaksanaan');
            $table->foreignId('upt_asal_id')->constrained('upts')->cascadeOnDelete();
            $table->foreignId('upt_tujuan_id')->constrained('upts')->cascadeOnDelete();
            $table->string('surat_usulan');
            $table->string('surat_persetujuan');
            $table->json('jumlah_personel')->nullable();
            $table->foreignId('jenis_pemindahan_id')->constrained('jenis_pemindahans')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_pemindahans');
    }
};
