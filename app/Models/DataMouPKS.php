<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataMouPks extends Model
{
    protected $table = 'data_mou_pks';

    protected $guarded = ['id'];

    protected $casts = [
        'riwayat_tahapan' => 'array',
        'masa_berlaku_mulai' => 'date',
        'masa_berlaku_selesai' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function upt()
    {
        return $this->belongsTo(Upt::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR OPTIONAL
    |--------------------------------------------------------------------------
    | Supaya link otomatis aman dipakai di frontend
    */

    public function getFileMouPksAttribute($value)
    {
        return $value ?: null;
    }

    public function getDokumentasiPenandatangananAttribute($value)
    {
        return $value ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER OPTIONAL
    |--------------------------------------------------------------------------
    */

    public function isSelesai()
    {
        return str_contains($this->status_tahapan, 'Selesai');
    }

    public function isDraft()
    {
        return !str_contains($this->status_tahapan, 'Selesai');
    }
}