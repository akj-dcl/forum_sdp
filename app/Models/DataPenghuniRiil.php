<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPenghuniRiil extends Model
{
    protected $table = 'data_penghuni_riils';

    // Daripada menulis $fillable satu per satu yang sangat panjang, 
    // kita pakai $guarded agar semua kolom otomatis bisa diisi kecuali ID.
    protected $guarded = ['id'];

    // Pastikan array casting untuk JSON
    protected $casts = [
        'jumlah_pengeluaran' => 'array',
    ];

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}