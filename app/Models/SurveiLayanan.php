<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveiLayanan extends Model
{
    protected $table = 'survei_layanans';

    protected $fillable = [
        'user_id',
        'upt_id',
        'nama_layanan',
        'status_berjalan',
        'sarana',
        'status_kelengkapan',
        'periode_pengadaan',
        'jumlah_perangkat',
        'status_perangkat',
        'foto_perangkat',
    ];

    protected $casts = [
        'status_berjalan' => 'boolean',
        'jumlah_perangkat' => 'integer',
        'foto_perangkat' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function upt()
    {
        return $this->belongsTo(Upt::class);
    }
}
