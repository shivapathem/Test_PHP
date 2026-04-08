<?php

namespace App\Http\Requests\ScheduledPeopleRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Form Request for updating Scheduled Person
 *
 * @package App\Http\Requests\ScheduledPeopleRequest
 */
class UpdateScheduledPersonRequest extends FormRequest
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
            'SPTeamID' => ['nullable', 'integer'],
            'DisplayName' => ['required', 'string', 'min:1', 'max:120'],
            'SchedulingTeamID' => ['required', 'integer', 'min:1'],
            'HomeTeamStartDate' => ['required', 'string'],
            'HomeTeamEndDate' => ['nullable', 'string'],
            'HomeTeamSortCode' => ['nullable', 'string', 'max:20'],
            'HomeTeamBackColour' => ['nullable', 'string', 'max:20'],
            'HomeTeamFontColour' => ['nullable', 'string', 'max:20'],
            'HomeTeamAdminNotes' => ['nullable', 'string', 'max:1000'],
            'HomeTeamFWANotes' => ['nullable', 'string', 'max:1000'],
            'DisplayFirstName' => ['nullable', 'string', 'max:50'],
            'DisplayLastName' => ['nullable', 'string', 'max:50'],
            'IsDefaultBGColour' => ['nullable', 'integer'],
            'IsAdditionalLeave' => ['nullable', 'integer'],
            'AdditionalTeamArray' => ['nullable', 'string'],
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
            'DisplayName.required' => 'Display Name is required.',
            'SchedulingTeamID.required' => 'Scheduling Team is required.',
            'SchedulingTeamID.min' => 'Please select a valid Scheduling Team.',
            'HomeTeamStartDate.required' => 'Home Team Start Date is required.',
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
            'SPTeamID' => 'Home Team ID',
            'DisplayName' => 'Display Name',
            'SchedulingTeamID' => 'Scheduling Team',
            'HomeTeamStartDate' => 'Home Team Start Date',
            'HomeTeamEndDate' => 'Home Team End Date',
            'HomeTeamSortCode' => 'Sort Code',
            'HomeTeamBackColour' => 'Background Colour',
            'HomeTeamFontColour' => 'Font Colour',
            'HomeTeamAdminNotes' => 'Admin Notes',
            'HomeTeamFWANotes' => 'FWA Notes',
            'DisplayFirstName' => 'First Name',
            'DisplayLastName' => 'Surname',
            'IsDefaultBGColour' => 'Default Background Colour',
            'IsAdditionalLeave' => 'Additional Leave',
            'AdditionalTeamArray' => 'Additional Teams',
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
