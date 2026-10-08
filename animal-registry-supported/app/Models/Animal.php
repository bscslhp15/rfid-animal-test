<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Animal extends Model
{
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'species_id',
        'pet_code',
        'group_name',
        'quantity',
        'attributes',
        'breed',
        'sex',
        'birthdate',
        'owner_name',
        'owner_phone',
        'owner_address',
        'status',
        'notes',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'quantity' => 'integer',
        'attributes' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $animal): void {
            if (blank($animal->pet_code)) {
                $animal->pet_code = self::generatePetCode();
            }
        });
    }

    public static function generatePetCode(): string
    {
        do {
            $petCode = now()->format('Ymd').strtoupper(Str::random(5));
        } while (static::where('pet_code', $petCode)->exists());

        return $petCode;
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function tag(): HasOne
    {
        return $this->hasOne(Tag::class);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class);
    }

    public function ownershipTransfers(): HasMany
    {
        return $this->hasMany(OwnershipTransfer::class)->orderBy('transferred_on');
    }
}
