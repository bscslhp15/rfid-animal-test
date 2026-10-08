<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnershipTransfer extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'animal_id',
        'from_owner_name',
        'from_owner_phone',
        'from_owner_address',
        'to_owner_name',
        'to_owner_phone',
        'to_owner_address',
        'transfer_type',
        'transferred_on',
        'price',
        'reference_no',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'transferred_on' => 'date',
        'price' => 'decimal:2',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
