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
        Schema::create('data_residivises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal'); // Relasi ke Lapas
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->foreignId('jenis_pidana_sekarang_id')->constrained('jenis_pidanas')->cascadeOnDelete();
            $table->integer('lama_pidana_sekarang');
            $table->string('tempat_pidana_sekarang');
            $table->string('jenis_pembebasan_sekarang');
            $table->foreignId('jenis_pidana_sebelumnya_id')->constrained('jenis_pidanas')->cascadeOnDelete();
            $table->integer('lama_pidana_sebelumnya');
            $table->string('tempat_pidana_sebelumnya');
            $table->string('jenis_pembebasan_sebelumnya');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_residivises');
    }
};
