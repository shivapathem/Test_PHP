<?php

namespace Database\Factories\Scheduling;

use App\Models\Scheduling\SchedulingGroup;
use App\Models\Scheduling\Division;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchedulingGroupFactory extends Factory
{
    protected $model = SchedulingGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'SchedulingGroupsName' => $this->faker->word,
            'DivisionID' => Division::factory(),
            'CreatedBy' => User::factory(),
            'UpdatedBy' => User::factory(),
            'IsIncludeINMenu' => $this->faker->boolean,
            'Notes' => $this->faker->sentence,
            'CreatedDate' => now(),
            'UpdatedDate' => now(),
        ];
    }
}
