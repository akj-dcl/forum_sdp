<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upt extends Model
{
    protected $fillable = [
        'kanwil_id',
        'name',
        'address',
        'is_active',
        'jenis_upts_id',
    ];

    public function jenisUpt(): BelongsTo
    {
        return $this->belongsTo(JenisUpt::class, 'jenis_upts_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'upt_id');
    }
}
