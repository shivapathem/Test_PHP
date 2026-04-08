<?php

namespace App\Http\Requests\Admin\AllocateUser;

use App\Models\User;
use App\Repositories\Contracts\Admin\AllocateUserRepositoryInterface;
use Illuminate\Foundation\Http\FormRequest;

class StoreAllocateUserRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'net_login' => [
                'required'
            ],
            'user_exists' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $userDetail = User::where('UD_NetLogin', $this->net_login)->first();
                    if ($userDetail != null) {
                        $fail($userDetail->UD_DisplayName);
                    }
                }
            ],
            'active_directory_check' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($this->net_login != null) {
                        // Call the method to check user details from Active Directory
                        $userDetailsFromAD = app()->make(AllocateUserRepositoryInterface::class)->getUserDetailsFromActiveDirectory($this->net_login);
                        if (count((array) $userDetailsFromAD) == 0) {
                            $fail('The Login has not been found. Please try again.');
                        }
                    }
                }
            ]
        ];
    }
}
