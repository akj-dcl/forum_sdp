<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPerawatanBulanan extends Model
{
    protected $table = 'data_perawatan_bulanans';

    protected $fillable = [
        'upt_id',
        'tanggal_input',
        'jumlah_penghuni_hiv',
        'jumlah_penghuni_tbc',
        'jumlah_penghuni_disabilitas',
        'jumlah_penghuni_jiwa',
        'jumlah_penghuni_tidakmenular',
        'jumlah_penghuni_menular',
        'jenis_penyakit_dominan',
        'jumlah_wbp_lansia',
        'jumlah_wbp_berkepanjangan',
        'jumlah_peserta_bpjs',
        'laporan'
    ];

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}