<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertanyaanSurvei extends Model
{
    protected $table = 'pertanyaan_surveis';

    protected $fillable = [
        'survei_id',
        'teks_pertanyaan',
        'tipe_jawaban',
        'opsi_pilihan',
        'wajib_diisi',
        'urutan',
    ];

    protected $casts = [
        'opsi_pilihan' => 'array',
        'wajib_diisi' => 'boolean',
        'urutan' => 'integer',
    ];

    public function survei()
    {
        return $this->belongsTo(MasterSurvei::class, 'survei_id');
    }
}
