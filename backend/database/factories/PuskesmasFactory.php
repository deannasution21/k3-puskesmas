<?php

namespace Database\Factories;

use App\Models\Puskesmas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Puskesmas>
 */
class PuskesmasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => 'UPT Puskesmas '.fake()->unique()->city(),
            'alamat' => fake()->address(),
        ];
    }
}
