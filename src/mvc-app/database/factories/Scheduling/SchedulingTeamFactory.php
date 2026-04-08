<?php

namespace Database\Factories\Scheduling;

use App\Models\Scheduling\SchedulingTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchedulingTeamFactory extends Factory
{
    protected $model = SchedulingTeam::class;

    public function definition()
    {
        return [
            'isActive' => 1,
            'createddate' => now(),
            'modifieddate' => now(),
        ];
    }
}
