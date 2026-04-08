<form id="scheduling-group-delete-form">
    <div class="scheduling-group-form-container">
        <div class="delete-popup-table">
            <div class="scheduling-group-row">
                <h2 class="scheduling-group-header">Delete Scheduling Group</h2>
            </div>
            <div class="scheduling-group-row">
                <div class="scheduling-group-cell validation-error-text">
                    Are you sure you want to delete "{{$schedulingGroup->SchedulingGroupsName}}"?
                </div>
            </div>
            <div class="scheduling-group-row">
                <div class="scheduling-group-cell">
                    @csrf
                    <button type="button" class="delete-popup" aria-label="Approve deletion scheduling group" id="approve-delete-scheduling-group-form" data-scheduling-group-id="{{$schedulingGroup->SchedulingGroupsID}}">Yes</button>
                    <button type="button" class="delete-popup" aria-label="Cancel deletion scheduling group" id="cancel-delete-scheduling-group-form">No</button>
                    <button type="button" class="delete-popup" aria-label="Ignore deletion scheduling group" id="ignore-delete-scheduling-group-form">Ok</button>
                </div>
                </div>
            </div>
        </div>
    </div>
</form>
