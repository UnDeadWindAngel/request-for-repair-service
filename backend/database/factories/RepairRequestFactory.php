<?php

namespace Database\Factories;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RepairRequest>
 */
class RepairRequestFactory extends Factory
{
    protected $model = RepairRequest::class;

    public function definition()
    {
        return [
            'clientName' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'problemText' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['new', 'assigned', 'in_progress', 'done', 'canceled']),
            'assignedTo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
