<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Models\Scheduling\Division;
use Illuminate\Support\Facades\Auth;

class UpdateSchedulingGroupRequest extends StoreSchedulingGroupRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $user = Auth::user();
        $schedulingGroup = $this->route('schedulingGroup');

        $allowedDivisionIds = $user->isSystemAdmin
            ? Division::pluck('DivisionID')->toArray()
            : $user->getAreasRoles->pluck('DivisionID')->toArray();

        // Override the group_name rule for update to ignore the current record
        $rules['group_name'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('SchedulingGroups', 'SchedulingGroupsName')
                ->ignore($schedulingGroup->SchedulingGroupsID, 'SchedulingGroupsID')
                ->where(fn ($q) => $q->where('DivisionID', $this->divisionid)->whereNull('DeletedAt')),
        ];

        // Override the divisionid rule to include allowed divisions
        $rules['divisionid'] = [
            'required',
            Rule::in($allowedDivisionIds),
        ];

        return $rules;
    }

    /**
     * Custom attribute names for validation messages.
     */
    public function attributes(): array
    {
        return [
            'group_name' => 'Scheduling Group Name',
            'divisionid' => 'Area',
            'allocations_menu' => 'Allocations Menu',
            'notes' => 'Notes',
        ];
    }
}
