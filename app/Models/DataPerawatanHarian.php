<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPerawatanHarian extends Model
{
    protected $table = 'data_perawatan_harians';

    protected $fillable = [
        'upt_id', 'tanggal_input',
        'jumlah_penghuni_berobat', 'jumlah_penghuni_dirawat', 'jumlah_wbp_meninggal',
        'jumlah_nama_berobatjalan', 'jumlah_nama_rawatklinik',
        'daftar_nama_berobatjalan', 'daftar_nama_rawatklinik',
        'dokumen'
    ];

    protected $casts = [
        'daftar_nama_berobatjalan' => 'array',
        'daftar_nama_rawatklinik' => 'array',
    ];

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}