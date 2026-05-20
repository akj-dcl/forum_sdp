<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_penghuni_riils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');

            // 1. Kekuatan Pengamanan
            $table->string('nama_regu');
            $table->integer('jumlah_regu')->default(0);
            $table->integer('jumlah_hadir')->default(0);
            $table->integer('jumlah_tidak_hadir')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('perwira_piket')->nullable();
            $table->integer('jumlah_bantuan_piket')->default(0);
            $table->string('pengawalan_rs')->nullable();
            $table->string('patroli_sambang')->nullable(); // TNI/POLRI
            $table->string('waktu_patroli_sambang')->nullable();

            // 2. Data Hunian (Napi & Tahanan dipisah sesuai PPT)
            $table->integer('jumlah_kapasitas')->default(0);
            $table->integer('jumlah_napi_laki')->default(0);
            $table->integer('jumlah_napi_perempuan')->default(0);
            $table->integer('jumlah_napi_anak')->default(0);
            $table->integer('jumlah_tahanan_laki')->default(0);
            $table->integer('jumlah_tahanan_perempuan')->default(0);
            $table->integer('jumlah_tahanan_anak')->default(0);
            
            $table->integer('total_isi')->default(0);
            $table->decimal('overcrowded', 8, 2)->default(0); // Decimal untuk persen %

            // 3. Pengeluaran (JSON)
            $table->json('jumlah_pengeluaran')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_penghuni_riils');
    }
};