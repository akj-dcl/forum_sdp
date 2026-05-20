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
        Schema::create('data_integrasi_tpps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            $table->date('tanggal_pelaksanaan');
            $table->text('rekomendiasi_sidang');
            $table->string('nomor_sidang');
            $table->integer('narapidana_lengkap');
            $table->integer('narapidana_belum');
            $table->text('kendala');
            $table->text('upaya');
            $table->string('berita_acara');
            $table->string('absensi');
            $table->string('dokumentasi_sidang');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_integrasi_tpps');
    }
};
