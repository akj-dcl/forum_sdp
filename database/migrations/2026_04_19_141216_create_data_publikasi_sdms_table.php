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
        Schema::create('data_publikasi_sdms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            $table->string('nama_kegiatan')->nullable();
            $table->date('tanggal_publikasi');
            $table->text('uraian_singkat')->nullable();
            $table->text('media_publikasi')->nullable();
            $table->string('link_berita')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_publikasi_sdms');
    }
};
