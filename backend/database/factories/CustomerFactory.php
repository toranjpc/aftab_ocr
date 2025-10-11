<?php

namespace Database\Factories;

use Modules\Collector\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $options = ['juridical', 'private'];

        $weights = [
            'juridical' => 99,
            'private' => 1,
        ];

        $type = $this->faker->randomElement($options, $weights);

        return [
            'name' => $this->faker->name,
            'family' => $this->faker->name,
            'title' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'code_eghtesadi' => random_int(1000000000, 9999999999),
            'shenase_meli' => $type === 'juridical' ? random_int(100000000000, 999999999999) : '0910240701',
            'shomare_sabt' => $type === 'juridical' ? random_int(1000000000, 9999999999) : null,
            'postal_code' => $this->faker->postcode,
            'address' => $this->faker->address,
            'type' => $type
        ];
    }
}
