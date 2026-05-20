<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_gangguan_kamtibs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            
            // Tambahan dari PPT
            $table->string('jenis_gangguan');
            $table->text('uraian_singkat');
            
            $table->date('tanggal_kejadian');
            $table->time('waktu_kejadian'); // Lebih baik pakai format time
            
            // Diubah ke JSON agar bisa beranak pinak inputannya
            $table->integer('jumlah_korban')->default(0);
            $table->json('detail_korban')->nullable();
            
            $table->integer('jumlah_barbuk')->default(0);
            $table->json('detail_barbuk')->nullable();
            
            $table->string('area_dirusak')->nullable();
            $table->text('pengamanan_tkp')->nullable();
            $table->text('upaya_dilakukan')->nullable();
            $table->text('permohonan_bantuan')->nullable();
            
            $table->string('dokumentasi'); // PDF
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_gangguan_kamtibs');
    }
};