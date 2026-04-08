<?php

namespace Database\Factories\Scheduling;

use App\Models\Scheduling\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition()
    {
        return [
            'DivisionName' => $this->faker->word,
            'isActive' => true,
        ];
    }
}
