<?php

namespace Database\Factories;

use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Species>
 */
class SpeciesFactory extends Factory
{
    protected $model = Species::class;

    public function definition(): array
    {
        $species = fake()->randomElement([
            ['name' => 'Dog', 'category' => 'companion'],
            ['name' => 'Cat', 'category' => 'companion'],
            ['name' => 'Cow', 'category' => 'livestock'],
            ['name' => 'Pig', 'category' => 'livestock'],
            ['name' => 'Horse', 'category' => 'livestock'],
            ['name' => 'Gamefowl', 'category' => 'gamefowl'],
        ]);

        return [
            'name' => $species['name'],
            'category' => $species['category'],
        ];
    }
}
