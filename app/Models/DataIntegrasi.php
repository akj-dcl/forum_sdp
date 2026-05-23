<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataIntegrasi extends Model
{
    protected $table = 'data_integrasis';

    protected $fillable = [
    'upt_id', 'tanggal_input',
    'tanggal_pelaksanaan_pb', 'jumlah_pb', 'sk_pb', 'dokumentasi_pb',
    'tanggal_pelaksanaan_cb', 'jumlah_cb', 'sk_cb', 'dokumentasi_cb',
    'tanggal_pelaksanaan_cmb', 'jumlah_cmb', 'sk_cmb', 'dokumentasi_cmb',
    'tanggal_pelaksanaan_asimilasi', 'jumlah_asimilasi', 'sk_asimilasi', 'dokumentasi_asimilasi',
    'tanggal_pelaksanaan_bebas_murni', 'jumlah_bebas_murni', 'sk_bebas_murni', 'dokumentasi_bebas_murni',
    'tanggal_pelaksanaan_cmk', 'jumlah_cmk', 'sk_cmk', 'dokumentasi_cmk',
];

protected $casts = [
    'dokumentasi_pb' => 'array',
    'dokumentasi_cb' => 'array',
    'dokumentasi_cmb' => 'array',
    'dokumentasi_asimilasi' => 'array',
    'dokumentasi_bebas_murni' => 'array',
    'dokumentasi_cmk' => 'array',
];

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}