<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSdmBulanan extends Model
{
    protected $table = 'data_sdm_bulanans';
    protected $guarded = ['id'];

    // Wajib: Ubah otomatis JSON ke Array
    protected $casts = [
        'jumlah_pegawai_bypendidikan' => 'array',
        'jumlah_pegawai_byusulan' => 'array',
        'jumlah_pejabat_struktural' => 'array',
        'jumlah_pegawai_bygolongan' => 'array',
        'jumlah_pegawai_bybeban' => 'array',
        'jumlah_petugas_penjagaan' => 'array',
    ];

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}