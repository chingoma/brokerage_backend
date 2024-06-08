<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sexes = ['male', 'female'];
        $firstname = fake()->firstName(fake()->shuffleArray($sexes)[0]);
        $lastname = fake()->firstName(fake()->shuffleArray($sexes)[0]);

        return [
            'region' => fake()->city(),
            'district' => fake()->city(),
            'ward' => fake()->city(),
            'place_birth' => fake()->city(),
            'title' => fake()->title(),
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'name' => $firstname.' '.$lastname,
            'gender' => fake()->shuffleArray(['male', 'female'])[0],
            'dob' => fake()->date(),
            'identity' => fake()->shuffle('234576504869032'),
            'country_id' => '221',
            'nationality' => 'tanzanian',
            'address' => fake()->address(),
            'mobile' => fake()->phoneNumber(),
            'email' => fake()->email(),
            'tin' => '999999999',
            'user_id' => '',
        ];
    }
}
