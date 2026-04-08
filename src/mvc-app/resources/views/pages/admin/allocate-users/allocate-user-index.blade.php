@extends('layouts.default')
@section('content')
@push('style-css')
<link rel="stylesheet" href="/mvc-app/public{{mix('css/admin/admin.css')}}" />
<link rel="stylesheet" href="/mvc-app/public{{mix('css/admin/allocate-users.css')}}" />
<link rel="stylesheet" href="/mvc-app/public{{mix('css/admin/user-info.css')}}" />
@endpush
<div id="allocate-user-container-tab" style="font-family: Verdana,Arial,sans-serif;">
    <ul class="allocate-user-tab" role="tablist" aria-label="Allocate User tabs" style=" background: #ccc;">
        <li data-list-type="allocate_user" role="presentation"><a href="#allocate_user" id="tab-allocate_user" role="tab" aria-controls="allocate_user" aria-selected="true" tabindex="0">Allocate Users</a></li>
        <li data-list-type="scheduled_staff" role="presentation"><a href="#scheduled_staff" id="tab-scheduled_staff" role="tab" aria-controls="scheduled_staff" aria-selected="false" tabindex="-1">Scheduled Staff</a></li>
        <li data-list-type="non_scheduled_staff" role="presentation"><a href="#non_scheduled_staff" id="tab-non_scheduled_staff" role="tab" aria-controls="non_scheduled_staff" aria-selected="false" tabindex="-1">Non Scheduled Staff</a></li>
        @if($showAreaPermissionsTab)
        <li data-list-type="area_permission" role="presentation"><a href="#area_permission" id="tab-area_permission" role="tab" aria-controls="area_permission" aria-selected="false" tabindex="-1">Area Permission</a></li>
        @endif
        <li data-list-type="permission_descriptions" role="presentation"><a href="#permission_descriptions" id="tab-permission_descriptions" role="tab" aria-controls="permission_descriptions" aria-selected="false" tabindex="-1" class="ui-tabs-anchor">Permission Descriptions</a></li>
    </ul>
    <div id="loading-spinner" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;">
        <img class="loader" src="/mvc-app/public/images/loading.gif" alt="Loading..." />
    </div>
    <div id="allocate_user" role="tabpanel" tabindex="0" aria-labelledby="tab-allocate_user" style="max-height: calc(100vh - 149px);padding: 0.2em 0em;">
    </div>
    <div id="scheduled_staff" role="tabpanel" tabindex="0" aria-labelledby="tab-scheduled_staff" style="padding: 10px 0px;">
    </div>
    <div id="non_scheduled_staff" role="tabpanel" tabindex="0" aria-labelledby="tab-non_scheduled_staff" style="padding: 0px !important;">
    </div>
    <div id="area_permission" role="tabpanel" tabindex="0" aria-labelledby="tab-area_permission">
    </div>
    <div id="permission_descriptions" role="tabpanel" tabindex="0" aria-labelledby="tab-permission_descriptions">
    </div>
</div>
@endsection
@push('scripts')
<script src="/mvc-app/public/{{mix('js/admin/allocate-users/allocate-users.js')}}" type="text/javascript"></script>
<script>
    $(function() {
        var allocateUserInstance = new AllocateUser({
            urls : {
                'getAllocateUserTabDataUrl': '{{route('admin.allocate-users.allocate-user-table')}}',
                'getAllocateUsersDataUrl': '{{route('admin.allocate-users.allocate-users-data')}}',
                'getUserInfoUrl': '{{route('admin.allocate-users.user-info', ['user' => ':userId'])}}',
                'getAreaPermissionsTabDataUrl': '{{route('admin.allocate-users.area-permissions-table')}}',
                'getAreaUsersUrl': '{{route('admin.allocate-users.get-area-users', ['areaId' => ':areaId'])}}',
                'getAddAllocateUserFormUrl': '{{route('admin.allocate-users.add-allocate-user-form')}}',
                'getStaffDetailsAutocompleteListUrl': '{{route('admin.allocate-users.get-staff-details-autocomplete-list')}}',
                'createAllocateUserUrl': '{{route('admin.allocate-users.create-allocate-user')}}',
                'getAreaUserHistoryUrl': '{{route('admin.allocate-users.area-user-history', ['areaId' => ':areaId', 'userRoleId' => ':userRoleId'])}}',
                'getAddAreaAllocateUserFormUrl': '{{route('admin.allocate-users.add-area-allocate-user-form')}}',
                'createAreaAllocateUserUrl': '{{route('admin.allocate-users.create-area-allocate-user')}}',
                'tabUrl': '{{route('admin.allocate-users.get-staff-view')}}',
                'searchStaffTeamUrl': '{{route('admin.allocate-users.search-staff-team')}}',
                'setUsersPermissionsUrl': '{{route('admin.allocate-users.set-users-permissions')}}',
                'removeStaffUrl': '{{route('admin.allocate-users.remove-staff')}}',
                'addNonScheduledStaffUrl': '{{route('admin.allocate-users.add-non-scheduled-staff')}}',
                'getAddNonScheduledStaffFormUrl': '{{route('admin.allocate-users.add-non-scheduled-staff-form')}}',
                'setDefaultTeamUrl': '{{route('admin.allocate-users.set-default-team')}}',
                'updateAreaUserRoleUrl': '{{route('admin.allocate-users.update-area-user-role')}}',
                'getAreaUserDetailsUrl': '{{route('admin.allocate-users.get-user-details')}}',
                'removeAreaUserUrl': '{{route('admin.allocate-users.remove-area-user')}}',
                'toggleAreaAdditionalRoleUrl': '{{route('admin.allocate-users.toggle-area-additional-role')}}',
                'getStaffHistoryUrl': '{{route('admin.allocate-users.get-staff-history')}}',
                'getPermissionDescriptionsTabDataUrl': '{{route('admin.allocate-users.permission-descriptions-table')}}',
            }
        });
    });
</script>
@endpush