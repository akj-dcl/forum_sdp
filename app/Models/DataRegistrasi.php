<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataRegistrasi extends Model
{
    protected $table = 'data_registrasis';
    
    protected $fillable = [
        'tanggal', 'upt_id', 
        'rekap_umum', 'rekap_pidsus', 'rekap_pidum', 
        'rekap_overstaying', 'rekap_integrasi', 'rekap_identitas', 
        'rekap_agama', 'rekap_pengeluaran',
        // Tambahan Fillable Baru
        'rekap_pendidikan', 'rekap_detail_napi', 'rekap_detail_tahanan',
        'rekap_petugas', 'rekap_pengunjung', 'rekap_wbp_dikunjungi',
        'rekap_wbp_vidcall', 'rekap_wbp_wartel', 'rekap_barang_titipan'
    ];

    protected $casts = [
        'rekap_umum' => 'array',
        'rekap_pidsus' => 'array',
        'rekap_pidum' => 'array',
        'rekap_overstaying' => 'array',
        'rekap_integrasi' => 'array',
        'rekap_identitas' => 'array',
        'rekap_agama' => 'array',
        'rekap_pengeluaran' => 'array',
        // Tambahan Casts Baru
        'rekap_pendidikan' => 'array',
        'rekap_detail_napi' => 'array',
        'rekap_detail_tahanan' => 'array',
        'rekap_petugas' => 'array',
        'rekap_pengunjung' => 'array',
        'rekap_wbp_dikunjungi' => 'array',
        'rekap_wbp_vidcall' => 'array',
        'rekap_wbp_wartel' => 'array',
        'rekap_barang_titipan' => 'array',
    ];

    public function upt() { 
        return $this->belongsTo(Upt::class); 
    }
}