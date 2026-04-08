<?php

namespace App\Http\Requests\Admin\AllocateUser;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAreaUserRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /*
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /*
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer',
            'area_id' => 'required|integer',
            'role_id' => 'required|integer'
        ];
    }
}
