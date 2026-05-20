<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPemindahan extends Model
{
    protected $table = 'data_pemindahans';

    protected $fillable = [
    'upt_id',
    'tanggal_input',
    'tanggal_pelaksanaan',
    'upt_asal_id',
    'upt_tujuan_id',
    'is_keluar_wilayah',
    'tujuan_luar_wilayah',
    'jenis_pemindahan_id',
    'detail_lain_lain',
    'jenis_kasus',
    'surat_usulan',
    'surat_persetujuan',
    'laporan_pemindahan',
    'foto_pemindahan',
    'jumlah_personel',
];

protected $casts = [
    'jumlah_personel' => 'array',
    'foto_pemindahan' => 'array',
    'is_keluar_wilayah' => 'boolean',
];

    public function upt() {
        return $this->belongsTo(Upt::class, 'upt_id');
    }

    public function uptAsal() {
        return $this->belongsTo(Upt::class, 'upt_asal_id');
    }

    public function uptTujuan() {
        return $this->belongsTo(Upt::class, 'upt_tujuan_id');
    }

    public function jenisPemindahan() {
        return $this->belongsTo(JenisPemindahan::class, 'jenis_pemindahan_id');
    }
}