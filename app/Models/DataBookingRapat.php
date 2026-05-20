<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataBookingRapat extends Model
{
    protected $table = 'data_booking_rapats';
    protected $guarded = ['id'];

    protected $casts = [
        'fasilitas' => 'array', // Casting JSON
    ];
}