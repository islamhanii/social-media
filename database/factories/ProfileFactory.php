<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'content' => str_replace("\\", "/", explode('uploads\\', fake()->image(public_path("uploads\users"), 2))[1]),
            'bio' => fake()->text(),
            'contact_details' => fake()->text(),
        ];
    }
}
