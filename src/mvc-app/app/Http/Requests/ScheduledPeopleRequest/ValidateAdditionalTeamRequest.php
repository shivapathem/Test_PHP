<?php

namespace App\Http\Requests\ScheduledPeopleRequest;

use Illuminate\Foundation\Http\FormRequest;

class ValidateAdditionalTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // apply policies/gates as needed
    }

    public function rules(): array
    {
        return [
            'prevaddteamarray'                     => ['nullable', 'array'],
            'prevaddteamarray.*.addteamsid'        => ['nullable', 'integer'],
            'prevaddteamarray.*.addteamstartdate'  => ['nullable', 'date_format:d-m-Y'],
            'prevaddteamarray.*.addteamenddate'    => ['nullable', 'date_format:d-m-Y'],

            'addteamSPTeamID'                      => ['nullable', 'integer'],
            'ddlteamsid'                           => ['required', 'integer'],
            'ddlteamsname'                         => ['nullable', 'string', 'max:150'],
            'schedulepersonid'                     => ['required', 'integer'],
            'addteamstartdate'                     => ['required', 'date_format:d-m-Y'],
            'addteamenddate'                       => ['nullable', 'date_format:d-m-Y'],
            'hometeamhiddenVal'                    => ['nullable', 'string'],
            'adduseractiontype'                    => ['nullable', 'in:add,edit,Add,Edit'],
            'hometeamStartDate'                    => ['nullable', 'date_format:d-m-Y'],
            'homestartdateHideNewTeam'             => ['nullable', 'date_format:d-m-Y'],
            'schDisplayName'                       => ['nullable', 'string', 'max:150'],
            'addteamdefaultbgcolour'               => ['nullable', 'string', 'max:20'],
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
            'addteamenddate.after_or_equal' => 'End date cannot be before start date.',

            // Home team date conflict messages
            'hometeamdate.conflict' => 'Additional Teams that you are trying to add having conflict of date with previous home team dates. Please select new Date that doesn\'t lie on or between (:startDate to :endDate) for the Additional Team or select any other team.',

            // Additional team already exists message (for date conflicts)
            'additionalteam.exists' => 'Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn\'t lie on or between (:startDate to :endDate) for the Additional Teams.',

            // Cannot change team when duties are allocated
            'team.hasduties' => 'You cannot change the Team for :displayName while he/she still has Duties allocated in the :teamName Team. Please open the Weeks Form and remove all the Duties that occur after and including the Effective From date you have chosen. The maximum Week that the person can be found in is - Week :maxWeek',

            // Start date before first home team
            'addteamstartdate.before_first_home_team' => 'Start Date of an Additional Team cannot be before the start date of the first Home Team. Please select a date on or after :minDate.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateDateRange($validator);
            $this->validateDuplicateAdditionalTeam($validator);
        });
    }
    /**
     * Validate that the additional team end date is not before the start date.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateDateRange($validator): void
    {
        $startDate = $this->input('addteamstartdate');
        $endDate   = $this->input('addteamenddate');

        if (empty($startDate) || empty($endDate)) {
            return;
        }

        $start = \Carbon\Carbon::createFromFormat('d-m-Y', $startDate);
        $end   = \Carbon\Carbon::createFromFormat('d-m-Y', $endDate);

        if ($end->lessThan($start)) {
            $validator->errors()->add(
                'addteamenddate',
                'End date cannot be before start date.'
            );
        }
    }
    /**
     * Validate no duplicate additional team is being added by checking against previous teams array.
     *
     * Prevents adding the same team ID if it already exists in prevaddteamarray.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateDuplicateAdditionalTeam($validator): void
    {
        $actionType        = strtolower($this->input('adduseractiontype', ''));
        $teamId            = (int) $this->input('ddlteamsid');
        $prevAddTeamArray  = $this->input('prevaddteamarray', []);

        if ($actionType !== 'add' || empty($prevAddTeamArray) || $teamId <= 0) {
            return;
        }

        foreach ($prevAddTeamArray as $prevTeam) {
            if (
                isset($prevTeam['addteamsid']) &&
                (int) $prevTeam['addteamsid'] === $teamId
            ) {
                $prevStartDate = $prevTeam['addteamstartdate'] ?? '';
                $prevEndDate   = $prevTeam['addteamenddate'] ?? '01-01-9999';

                $errorMessage = str_replace(
                    [':startDate', ':endDate'],
                    [$prevStartDate, $prevEndDate],
                    'Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn\'t lie on or between (:startDate to :endDate) for the Additional Teams.'
                );

                $validator->errors()->add('ddlteamsid', $errorMessage);
                return;
            }
        }
    }
}
