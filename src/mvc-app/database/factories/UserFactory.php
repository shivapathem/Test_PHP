<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'UD_EmpNumber' => $this->faker->numberBetween(1000, 9999),
            'UD_StaffNumber' => $this->faker->bothify('#######'),
            'UD_NetLogin' => $this->faker->userName(),
            'UD_DisplayName' => $this->faker->name(),
            'UD_DisplayFirstName' => $this->faker->firstName(),
            'UD_DisplayLastName' => $this->faker->lastName(),
            'UD_PreferredFirstName' => $this->faker->firstName(),
            'UD_InternalEmail' => $this->faker->safeEmail(),
            'UD_ExternalEmail' => $this->faker->safeEmail(),
            'UD_PersonalPhone' => $this->faker->phoneNumber(),
            'UD_AdminNotes' => $this->faker->text(200),
            'UD_FWANotes' => $this->faker->text(200),
            'UD_PHLLeaveAmount' => $this->faker->randomFloat(2, 0, 20),
            'UD_IsEligibleForAdditionalLeave' => $this->faker->boolean(),
            'UD_OfficePhone' => $this->faker->phoneNumber(),
            'UD_OfficeExtension' => $this->faker->numerify('####'),
            'UD_TeampayStaffID' => $this->faker->numberBetween(1000, 9999),
            'UD_StartDate' => $this->faker->date(),
            'UD_Status' => $this->faker->numberBetween(0, 5),
            'UD_LastLoginDate' => now(),
            'UD_CreatedBy' => 1,
            'UD_CreatedDate' => now(),
            'UD_UpdatedBy' => 1,
            'UD_UpdatedDate' => now(),
        ];
    }
}
