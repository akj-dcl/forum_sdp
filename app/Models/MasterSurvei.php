<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSurvei extends Model
{
    protected $table = 'master_surveis';

    protected $fillable = [
        'user_id',
        'channel_id',
        'judul',
        'deskripsi',
        'batas_pengisian',
        'tanggal_mulai',
        'tanggal_selesai',
        'target_roles',
        'is_active',
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'target_roles' => 'array',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pertanyaans()
    {
        return $this->hasMany(PertanyaanSurvei::class, 'survei_id');
    }

    public function respons()
    {
        return $this->hasMany(ResponSurvei::class, 'survei_id');
    }
}
