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
Schema::create('data_sarpras_keamanans', function (Blueprint $table) {
$table->id();
$table->foreignId('upt_id')->constrained('upts')->cascadeOnDelete();
$table->date('tanggal_input');
// Tambahan dari PPT
$table->integer('jumlah_senjata_api_baik')->default(0);
$table->integer('jumlah_senjata_api_rusak')->default(0);
$table->integer('jumlah_xray')->default(0);
$table->integer('jumlah_body_scanner')->default(0);
$table->integer('jumlah_phh')->default(0);
$table->integer('jumlah_borgol')->default(0);
$table->integer('jumlah_cctv')->default(0);
$table->integer('jumlah_apar')->default(0);
$table->integer('jumlah_lonceng')->default(0);
$table->integer('jumlah_ht')->default(0);
$table->timestamps();
});
}

/**
* Reverse the migrations.
*/
public function down(): void
{
Schema::dropIfExists('data_sarpras_keamanans');
}
};