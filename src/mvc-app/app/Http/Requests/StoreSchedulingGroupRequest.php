<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Scheduling\Division;
use Illuminate\Support\Facades\Auth;

class StoreSchedulingGroupRequest extends FormRequest
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
        $user = Auth::user();

        $allowedDivisionIds = $user->isSystemAdmin
            ? Division::pluck('DivisionID')->toArray()
            : $user->getAreasRoles->pluck('DivisionID')->toArray();

        return [
            'group_name' => [
                'required',
                'string',
                'max:255',
                'unique:SchedulingGroups,SchedulingGroupsName',
            ],
            'divisionid' => [
                'required',
                Rule::in($allowedDivisionIds),
            ],
            'allocations_menu' => 'required|in:0,1',
            'notes' => 'nullable|string|max:1000',
        ];
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
