<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'type',
        'severity',
        'animal_id',
        'title',
        'message',
        'due_on',
        'dedupe_key',
        'status',
        'triggered_at',
        'read_at',
    ];

    protected $casts = [
        'due_on' => 'date',
        'triggered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
}
