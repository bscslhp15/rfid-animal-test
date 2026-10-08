<?php

namespace App\Models;

use Database\Factories\SpeciesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Species extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category'];

    protected static function newFactory(): SpeciesFactory
    {
        return SpeciesFactory::new();
    }

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }
}
