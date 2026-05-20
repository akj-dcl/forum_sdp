<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataIntegrasiTpp extends Model
{
    protected $table = 'data_integrasi_tpps';

    protected $fillable = [
    'upt_id', 'tanggal_input', 'tanggal_pelaksanaan', 'nomor_sidang', 
    'jumlah_narapidana_sidang', // <--- Tambahkan ini
    'rekomendasi_sidang', 'permasalahan', 'upaya', 
    'berita_acara', 'absensi', 'dokumentasi_sidang'
];

protected $casts = [
    'dokumentasi_sidang' => 'array', // Penting untuk multiple images
];

 public function upt()
    {
        return $this->belongsTo(Upt::class);
    }
}