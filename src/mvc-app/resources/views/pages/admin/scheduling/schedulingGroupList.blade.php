@extends('layouts.default')
@section('content')
    <div id="page-container">
        <output id="filter-status"
        aria-live="polite"
        aria-atomic="true"
        class="acc-only" ></output>
        <button class="ui-button ui-widget ui-corner-all addbtn" id="create-scheduling-button">
                <img src="/mvc-app/public/images/button_add.png" alt="">&nbsp;Add Scheduling Group
        </button>
        <div class="tableheadersmall medtextboldcentre screen-fix-withscroll list-view">
            <h1 style="font-size: 11px; padding-top: 10px; margin: 0px;">Scheduling Groups</h1>
            <p style="padding-top: 2px;">Displaying a list of all Scheduling Groups.</p>
        </div>
        <table id="scheduling-list-table" class="display tablesmall">
            <thead>
                <tr>
                    <th style="text-align: center">Actions</th>
                    <th>Area Name</th>
                    <th>Scheduling Group Name</th>
                    <th>Associated Scheduling Team</th>
                    <th>Allocations Menu</th>
                    <th style="line-height:28px;">Notes</th>
                    <th>Last Amended By</th>
                    <th>Last Amended Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($schedulingGroups as $group)
                    <tr>
                        <td style="text-align: center">
                            <div style="width: 90px">
                                <i class="fa fa-hourglass-end history-scheduling-group-icon" data-scheduling-group-id="{{$group->SchedulingGroupsID}}" role="button" aria-label="View scheduling group history" tabindex="0"></i>
                                <i class="fas fa-edit edit-scheduling-group-icon" data-scheduling-group-id="{{$group->SchedulingGroupsID}}" role="button" aria-label="Edit scheduling group" tabindex="0"></i>
                                <i class="fas fa-trash-alt delete-scheduling-group-icon" data-scheduling-group-id="{{$group->SchedulingGroupsID}}" role="button" aria-label="Delete scheduling group" tabindex="0"></i>
                            </div>
                        </td>
                        <td>{{ $group->area ? ucfirst($group->area->DivisionName) : 'N/A' }}</td>
                        <td class="scheduling-group-name" data-scheduling-group-id="{{$group->SchedulingGroupsID}}">{{$group->SchedulingGroupsName}}</td>
                        <td>{{$group->schedulingTeams->pluck('schedulingTeamName')->filter()->implode(', ') ?: 'None'}}</td>
                        <td>{{$group->IsIncludeINMenu ? 'Yes' : 'No' }}</td>
                        <td>{{$group->Notes }}</td>
                        <td>{{$group->updatedBy->UD_DisplayName ?? 'N/A'}}</td>
                        <td>{{$group->UpdatedDate->format('d/m/Y H:i')}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div id="create-scheduling-group">
    </div>
@endsection
@push('scripts')
<script src="/mvc-app/public/{{mix('js/scheduling/scheduling.js')}}" type="text/javascript"></script>
<script>
    $(function () {
        new SchedulingVar({
            createUrl: '{{ route('scheduling-group.create') }}',
            storeUrl: '{{ route('scheduling-group.store') }}',
            editUrl: '{{ route('scheduling-group.edit', [':schedulingGroup']) }}',
            updateUrl: '{{ route('scheduling-group.update', [':schedulingGroup']) }}',
            deleteFormUrl: '{{ route('scheduling-group.deleteForm', [':schedulingGroup']) }}',
            deleteUrl: '{{ route('scheduling-group.destroy', [':schedulingGroup']) }}',
            schedulingGroupHistoryUrl: '{{ route('scheduling-group.history', [':schedulingGroup']) }}',
        });
    });
</script>
@endpush
