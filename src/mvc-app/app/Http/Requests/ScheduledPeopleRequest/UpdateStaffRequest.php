<?php

namespace App\Http\Requests\ScheduledPeopleRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Form Request for updating Staff Details
 *
 * @package App\Http\Requests\ScheduledPeopleRequest
 */
class UpdateStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'FirstName' => ['nullable', 'string', 'max:50'],
            'Surname' => ['nullable', 'string', 'max:50'],
            'MiddleName' => ['nullable', 'string', 'max:50'],
            'PreferredName' => ['nullable', 'string', 'max:50'],
            'Designation' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'FirstName.max' => 'First Name cannot exceed 50 characters.',
            'Surname.max' => 'Surname cannot exceed 50 characters.',
            'MiddleName.max' => 'Middle Name cannot exceed 50 characters.',
            'PreferredName.max' => 'Preferred Name cannot exceed 50 characters.',
            'Designation.max' => 'Designation cannot exceed 100 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'FirstName' => 'First Name',
            'Surname' => 'Surname',
            'MiddleName' => 'Middle Name',
            'PreferredName' => 'Preferred Name',
            'Designation' => 'Designation',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        $errorMessages = [];

        foreach ($errors as $field => $messages) {
            $errorMessages[$field] = $messages[0];
        }

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $errorMessages
            ], 422)
        );
    }
}
