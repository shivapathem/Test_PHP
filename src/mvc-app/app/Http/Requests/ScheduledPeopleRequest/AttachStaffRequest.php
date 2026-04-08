<?php

namespace App\Http\Requests\ScheduledPeopleRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Form Request for attaching Staff to a Scheduled Person
 *
 * @package App\Http\Requests\ScheduledPeopleRequest
 */
class AttachStaffRequest extends FormRequest
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
        $actionType = $this->input('actionType', 'select');

        // For cancel action, staff ID is not required
        if ($actionType === 'cancel') {
            return [
                'selectedstaffID' => ['nullable', 'integer', 'min:0'],
                'actionType' => ['nullable', 'string', 'in:save,select,cancel'],
                'schedPersonID' => ['nullable', 'integer', 'min:0'],
                'oldschedPersonID' => ['nullable', 'integer', 'min:0'],
            ];
        }

        return [
            'selectedstaffID' => ['required', 'integer', 'min:1'],
            'actionType' => ['nullable', 'string', 'in:save,select,cancel'],
            'schedPersonID' => ['nullable', 'integer', 'min:0'],
            'oldschedPersonID' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Map legacy field names to standard names
        $this->merge([
            'selectedstaffID' => $this->input('selectedstaffID'),
            'schedPersonID' => $this->input('schedPersonID', 0),
            'oldschedPersonID' => $this->input('oldschedPersonID', 0),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'selectedstaffID.required' => 'Staff ID is required.',
            'selectedstaffID.integer' => 'Staff ID must be a valid integer.',
            'selectedstaffID.min' => 'The selected staff ID must be at least 1.',
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
            'selectedstaffID' => 'Staff ID',
            'actionType' => 'Action Type',
            'oldschedPersonID' => 'Previous Scheduled Person ID',
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
