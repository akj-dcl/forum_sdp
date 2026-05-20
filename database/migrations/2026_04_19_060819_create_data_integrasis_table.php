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
        Schema::create('data_integrasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            $table->date('tanggal_pelaksanaan_pb');
            $table->integer('jumlah_pb');
            $table->string('sk_pb');
            $table->string('dokumentasi_pb');
            $table->date('tanggal_pelaksanaan_cb');
            $table->integer('jumlah_cb');
            $table->string('sk_cb');
            $table->string('dokumentasi_cb');
            $table->date('tanggal_pelaksanaan_cmb');
            $table->integer('jumlah_cmb');
            $table->string('sk_cmb');
            $table->string('dokumentasi_cmb');
            $table->date('tanggal_pelaksanaan_asimilasi');
            $table->integer('jumlah_asimilasi');
            $table->string('sk_asimilasi');
            $table->string('dokumentasi_asimilasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_integrasis');
    }
};
