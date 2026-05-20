<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataResidivis extends Model
{
    protected $table = 'data_residivises';
    
    protected $fillable = [
        'upt_id', 'tanggal', 'nama', 'alamat', 
        'jenis_pidana_sekarang_id', 'lama_pidana_sekarang', 'tempat_pidana_sekarang', 
        'berapa_kali_dipidana', 'putusan_pengadilan', // Field Baru
        'jenis_pidana_sebelumnya_id', 'lama_pidana_sebelumnya', 'tempat_pidana_sebelumnya', 'jenis_pembebasan_sebelumnya'
    ];
    
    public function upt() { return $this->belongsTo(Upt::class); }
    public function jenis_pidana_sekarang() { return $this->belongsTo(JenisPidana::class, 'jenis_pidana_sekarang_id'); }
    public function jenis_pidana_sebelumnya() { return $this->belongsTo(JenisPidana::class, 'jenis_pidana_sebelumnya_id'); }
}