<form id="scheduling-form">
    <div class="scheduling-group-form-container">
        <div class="scheduling-group-row">
            <h2 class="scheduling-group-header">{{isset($schedulingGroup) ? 'Update Scheduling Group' : 'Add Scheduling Group'}}</h2>
        </div>
        <div class="scheduling-group-row">
            <div class="scheduling-group-cell scheduling-group-cell-left">Area<span class="required-asterisk">*</span></div>
                <div class="scheduling-group-cell">
                    <select data-placeholder="Select Area" id="divisionid" name="divisionid" style="width:258px" {{ count($divisions) > 1 ? 'required aria-required="true"' : '' }} aria-label="Area Selection" role="listbox" {{ !isset($schedulingGroup) && count($divisions) == 1 ? 'disabled' : '' }}>
                        <option value="" disabled>Select Area</option>
                            @foreach ($divisions as $key => $division)
                                <option value="{{ $division->DivisionID }}" {{ (isset($schedulingGroup) && $schedulingGroup->DivisionID == $division->DivisionID) || (!isset($schedulingGroup) && count($divisions) == 1) ? 'selected' : '' }}>
                                    {{ $division->DivisionName }}
                                </option>
                            @endforeach
                    </select>
                    @if(!isset($schedulingGroup) && count($divisions) == 1)
                        <input type="hidden" name="divisionid" value="{{ $divisions->first()->DivisionID }}">
                    @endif
                </div>
        </div>
        <div class="scheduling-group-row">
            <div class="scheduling-group-cell scheduling-group-cell-left">Scheduling Group Name<span class="required-asterisk">*</span></div>
                <div class="scheduling-group-cell">
                    <input type="text" style="width:250px" id="group_name" name="group_name" size="50"  value="{{isset($schedulingGroup) ? $schedulingGroup->SchedulingGroupsName : ''}}" required aria-required="true" autocomplete="off" @include('includes.input-text-remove-special-char-regex')>
                </div>
        </div>
        <div class="scheduling-group-row">
            <div class="scheduling-group-cell scheduling-group-cell-left">Allocations Menu<span class="required-asterisk">*</span></div>
                <div class="scheduling-group-cell">
                    <select data-placeholder="Select Area" id="allocations_menu" name="allocations_menu" style="width:258px" required aria-required="true" aria-label="Allocations Menu Selection" role="listbox">
                        <option value="1" {{ isset($schedulingGroup) && $schedulingGroup->IsIncludeINMenu == 1 ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ isset($schedulingGroup) && $schedulingGroup->IsIncludeINMenu == 0 ? 'selected' : '' }}>No</option>
                    </select>
                </div>
        </div>
        <div class="scheduling-group-row">
            <div class="scheduling-group-cell scheduling-group-cell-left">Notes</div>
                <div class="scheduling-group-cell">
                    <textarea style="width:254px" name="notes" class="form-control" rows="2">{{ isset($schedulingGroup) ? $schedulingGroup->Notes : '' }}</textarea>
                </div>
        </div>
        <div class="scheduling-group-row">
            <div class="scheduling-group-cell scheduling-group-cell-left"></div>
                <div class="scheduling-group-cell">
                    @csrf
                    @if(!isset($schedulingGroup))
                    <button type="button" id="submit-create-scheduling-form" aria-label="Add scheduling group">Submit</button>
                    @else
                    <button type="button" id="submit-update-scheduling-form" aria-label="Update scheduling group">Update</button>
                    @endif
                </div>
            </div>
        </div>
</form>
