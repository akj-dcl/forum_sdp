<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_remisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
            $table->date('tanggal_input');
            
            // JSON ini akan menyimpan banyak jenis remisi dalam 1 laporan harian
            $table->json('detail_remisi'); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_remisis');
    }
};