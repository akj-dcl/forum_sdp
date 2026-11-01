<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleTier extends Model
{
    protected $table = 'role_tiers';
    public $timestamps = false;
    protected $fillable = ['nama_tier'];
}
