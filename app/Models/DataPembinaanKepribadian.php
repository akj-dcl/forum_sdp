<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPembinaanKepribadian extends Model
{
    protected $table = 'data_pembinaan_kepribadians';

    // 👇 INI YANG BIKIN ERROR: Sebelumnya lupa pakai "_id"
    protected $fillable = [
        'upt_id', 
        'jenis_kepribadian_id', // Harus ada _id nya
        'detail_lain_lain',
        'tanggal', 
        'nama_kegiatan', 
        'pemateri', 
        'jumlah_peserta', 
        'hasil_kegiatan', 
        'dokumentasi_kegiatan', 
        'rekomendasi_kegiatan'
    ];

    // Beritahu Laravel kalau dokumentasi itu isinya Array (banyak file)
    protected $casts = [
        'dokumentasi_kegiatan' => 'array',
    ];

    public function upt() { 
        return $this->belongsTo(Upt::class); 
    }
    
    // Pastikan J-nya besar untuk penamaan Model dan arahkan foreign key-nya
    public function jenis_kepribadian() { 
        return $this->belongsTo(JenisKepribadian::class, 'jenis_kepribadian_id'); 
    }
}