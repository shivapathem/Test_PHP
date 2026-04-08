<?php

namespace App\Http\Requests\ScheduledPeopleRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Form Request for searching Staff Details
 *
 * @package App\Http\Requests\ScheduledPeopleRequest
 */
class SearchStaffRequest extends FormRequest
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
            'selectedForeName' => ['nullable', 'string', 'max:50'],
            'selectedNetLogin' => ['nullable', 'string', 'max:100'],
            'selectedSurName' => ['nullable', 'string', 'max:50'],
            'selectedStaffNumber' => ['nullable', 'string', 'max:20'],
            'actionType' => ['nullable', 'string', 'in:search,select,clearfilter'],
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
            'actionType.in' => 'Invalid action type.',
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
            'selectedForeName' => 'First Name',
            'selectedNetLogin' => 'Network ID',
            'selectedSurName' => 'Surname',
            'selectedStaffNumber' => 'Staff Number',
            'actionType' => 'Action Type',
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
