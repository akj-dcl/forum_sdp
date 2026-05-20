<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPengawalanPengamanan extends Model
{
    protected $table = 'data_pengawalan_pengamanans';
    protected $guarded = ['id'];

    protected $casts = [
        'detail_wbp_dikawal' => 'array',
        'detail_petugas_pengawalan' => 'array',
    ];

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
    public function jenisPengawalan() {
        return $this->belongsTo(JenisPengawalan::class, 'jenis_pengawalan_id');
    }
    public function jenisKlasifikasi() {
        return $this->belongsTo(JenisKlasifikasi::class, 'jenis_klasifikasi_id');
    }
}