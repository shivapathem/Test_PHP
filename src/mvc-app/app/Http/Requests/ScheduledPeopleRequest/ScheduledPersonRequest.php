<?php

namespace App\Http\Requests\ScheduledPeopleRequest;

use Illuminate\Foundation\Http\FormRequest;

class ScheduledPersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Main identifiers
            'SPTeamID'              => ['nullable', 'integer'],
            'ScheduledPersonID'     => ['nullable', 'integer'],
            'ActionType'            => ['nullable', 'string', 'in:create,edit'],

            // Display names
            'DisplayName'           => ['required', 'string', 'max:120'],
            'DisplayFirstName'      => ['nullable', 'string', 'max:50'],
            'DisplayLastName'       => ['nullable', 'string', 'max:50'],

            // Home team details
            'SchedulingTeamID'      => ['required', 'integer', 'min:1'],
            'HomeTeamStartDate'     => ['required', 'string'],
            'HomeTeamEndDate'       => ['nullable', 'string'],
            'HomeTeamSortCode'      => ['nullable', 'string', 'max:20'],
            'HomeTeamBackColour'    => ['nullable', 'string', 'max:20'],
            'HomeTeamFontColour'    => ['nullable', 'string', 'max:20'],

            // Notes
            'HomeTeamAdminNotes'    => ['nullable', 'string', 'max:1000'],
            'HomeTeamFWANotes'      => ['nullable', 'string', 'max:1000'],

            // Options
            'IsDefaultBGColour'     => ['nullable', 'integer'],
            'IsAdditionalLeave'    => ['nullable', 'integer'],

            // Additional teams
            'AdditionalTeamArray'   => ['nullable', 'string'],
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
            'DisplayName.required' => 'The display name field is required.',
            'SchedulingTeamID.required' => 'Please select a home team.',
            'SchedulingTeamID.min' => 'Please select a home team.',
            'HomeTeamStartDate.required' => 'The start date field is required.',
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
            'SPTeamID' => 'Team ID',
            'DisplayName' => 'Display Name',
            'DisplayFirstName' => 'Display First Name',
            'DisplayLastName' => 'Display Last Name',
            'SchedulingTeamID' => 'Home Team ID',
            'HomeTeamStartDate' => 'Start Date',
            'HomeTeamEndDate' => 'End Date',
            'HomeTeamSortCode' => 'Sort Code',
            'HomeTeamBackColour' => 'Background Colour',
            'HomeTeamFontColour' => 'Font Colour',
            'HomeTeamAdminNotes' => 'Admin Notes',
            'HomeTeamFWANotes' => 'FWA Notes',
            'IsDefaultBGColour' => 'Default Background',
            'IsAdditionalLeave' => 'Additional Leave',
            'AdditionalTeamArray' => 'Additional Teams',
        ];
    }
}
