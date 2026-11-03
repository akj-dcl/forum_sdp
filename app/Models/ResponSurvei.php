<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponSurvei extends Model
{
    protected $table = 'respon_surveis';

    protected $fillable = [
        'survei_id',
        'user_id',
        'upt_id',
        'jawaban',
        'total_skor',
    ];

    protected $casts = [
        'jawaban' => 'array',
        'total_skor' => 'integer',
    ];

    public function survei()
    {
        return $this->belongsTo(MasterSurvei::class, 'survei_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function upt()
    {
        return $this->belongsTo(Upt::class);
    }
}
