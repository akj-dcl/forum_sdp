<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataGangguanKamtib extends Model
{
    protected $table = 'data_gangguan_kamtibs';
    protected $guarded = ['id']; // Mempermudah mass assignment

    protected $casts = [
        'detail_korban' => 'array',
        'detail_barbuk' => 'array',
    ];

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}