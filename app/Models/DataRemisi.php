<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataRemisi extends Model
{
    protected $table = 'data_remisis';

    protected $fillable = [
        'upt_id',
        'tanggal_input',
        'detail_remisi', // Cukup panggil ini saja
    ];

    protected $casts = [
        'detail_remisi' => 'array', // Wajib agar Laravel otomatis memproses JSON
    ];

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}