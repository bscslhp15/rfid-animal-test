<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tag extends Model
{
    protected $fillable = [
        'animal_id',
        'identifier',
        'type',
        'status',
        'assigned_at',
        'deactivated_at',
        'deactivation_reason',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
}
