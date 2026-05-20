<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPembinaanKemandirian extends Model
{
    protected $table = 'data_pembinaan_kemandirians';

    protected $fillable = [
        'upt_id', 
        'jenis_kemandirian_id',
        'detail_lain_lain', // Tambahan Baru
        'tanggal', 
        'nama_kegiatan', 
        'jumlah_peserta', 
        'hasil_kegiatan', 
        'rekomendasi_kegiatan',
        'dokumentasi_kegiatan'
    ];

    protected $casts = [
        'dokumentasi_kegiatan' => 'array',
    ];

    public function upt() { 
        return $this->belongsTo(Upt::class); 
    }
    
    public function jenis_kemandirian() { 
        return $this->belongsTo(JenisKemandirian::class, 'jenis_kemandirian_id'); 
    }
}