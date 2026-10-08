<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedingLog extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'animal_id',
        'feed_type',
        'quantity',
        'unit',
        'fed_at',
        'cost',
        'notes',
    ];

    protected $casts = [
        'fed_at' => 'datetime',
        'quantity' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
}
