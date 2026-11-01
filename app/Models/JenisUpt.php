<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisUpt extends Model
{
    protected $table = 'jenis_upts';
    public $timestamps = false;
    protected $fillable = ['nama_upt'];

    public function upts(): HasMany
    {
        return $this->hasMany(Upt::class, 'jenis_upts_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'jenis_upts_id');
    }
}
