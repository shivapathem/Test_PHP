@extends('layouts.default')
@section('content')
<link rel="stylesheet" href="/mvc-app/public/{{mix('css/facility/facility.css')}}" />
<div id="facility-tabs">
    <h1 class="acc-only" >Facility Catalogue</h1>
    <ul>
        <li><a href="#facility-tabs-facility-catalogue">Facility Catalogue</a></li>
    </ul>
    
    <div id="facility-actions-bar"
        class="tableheadersmall medtextboldcentre screen-fix-withscroll"
        style="padding: 10px; display: flex; gap: 12px; align-items: center; margin-top: 5px;"
        >

        @can('create', \App\Models\Facility::class)
            <button class="ui-button ui-widget ui-corner-all addbtn"
                    id="create-facility-button"
                    type="button"
                    aria-label="Add Facility">
                <img src="/mvc-app/public/images/button_add.png" alt="">
                <span aria-hidden="true">&nbsp;Add Facility</span>
            </button>
        @endcan

        @include('pages.facility.facilityFilter')
    </div>

    <div id="facility-loader" style="display: none;padding:10px;">
        <div id="spinner-container" style="display: flex;flex-direction:row;gap:18px;margin-left:40%">
            <div class="loader-spinner"></div>
            <span>Loading Facilities...</span>
        </div>
    </div>
    <div id="facility-tabs-facility-catalogue">
        <div>  
            <table id="facility-list-table" class="stripe cell-border nowrap dataTable">
                <thead>
                    <tr>
                        <th scope="col">Action</th>
                        <th scope="col">Facility Name</th>
                        <th scope="col">Status</th>
                        <th scope="col">Area Owner</th>
                        <th scope="col">Restrict Bookers</th>
                        <th scope="col">Provider Type</th>
                        <th scope="col">Provider Name</th>
                        <th scope="col">Facility Type</th>
                        <th scope="col">Facility Sub Type</th>
                        <th scope="col">Primary Facility</th>
                        <th scope="col">Location</th>
                        <th scope="col">Default Booking</th>
                        <th scope="col">Facility Capacity</th>
                        <th scope="col">Accessible?</th>
                        <th scope="col">Service</th>
                        <th scope="col">Equipment</th>
                        <th scope="col">Facility Administrator</th> {{--  Will be hidden used for filter--}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($facilityList as $facilityModel)
                    @php($facilityAdministrator = auth()->user()->isFacilityAdministrator($facilityModel))
                    @php($booker = auth()->user()->haveAccessToFacility($facilityModel)) 
                    @php($facilityAreaBelong = auth()->user()->haveAreaTeamFacility($facilityModel))                 
                        <tr>
                            <td>
                                <div style="width: 90px">
                                <i class="fa fa-eye view-facility-icon" data-facility-id="{{$facilityModel->FC_FacilityID}}" role="button" aria-label="View facility" tabindex="0" title="View facility"></i>
                                @if($facilityAdministrator)
                                    @can('update', $facilityModel)
                                    <i class="fa fa-edit edit-facility-icon" data-facility-id="{{$facilityModel->FC_FacilityID}}" role="button" aria-label="Edit facility" tabindex="0" title="Edit facility"></i>
                                    @endcan

                                    <i class="fas fa-trash-alt delete-facility-icon" data-facility-id="{{$facilityModel->FC_FacilityID}}" role="button" aria-label="Delete facility" tabindex="0" title="Delete facility"></i>

                                    @if($facilityModel->FC_ArchivedDate != null)
                                    <i id="active-facility-icon" class="fas fa-refresh" data-facility-id="{{$facilityModel->FC_FacilityID}}" role="button" aria-label="Activate facility" tabindex="0" title="Activate facility"></i>
                                    @else
                                    <i id="archive-facility-icon" class="fas fa-archive" data-facility-id="{{$facilityModel->FC_FacilityID}}" role="button" aria-label="Archive facility" tabindex="0" title="Archive facility"></i>
                                    @endif
                                    @can('facilityAdminReal', $facilityModel)
                                    <i class="fa fa-users facility-administrator-icon" data-facility-id="{{$facilityModel->FC_FacilityID}}" role="button" aria-label="Facility Administrator" tabindex="0" title="Facility Administrator"></i>
                                    @endif
                                @endif
                                @if($booker)
                                <i class="fa fa-hourglass-end history-facility-icon" data-facility-id="{{$facilityModel->FC_FacilityID}}" role="button" aria-label="View facility history" tabindex="0" title="View facility history"></i>
                                @endif
                                </div>
                            </td>
                            <th scope="row" style="text-align: left; font-size: 12px;">{{$facilityModel->FC_FacilityName}}</th>
                            <td>{{$facilityModel->current_status}}</td>
                            <td>{{$facilityModel->facilityAreaOwner->DivisionName}}</td>
                            <td>
                            @if($facilityModel->FC_DefaultBookingType == App\Models\Facility\Facility::DEFAULT_BOOKING_SELF_BOOKED)
                                All users are Requestors
                            @else
                            {{
                            $facilityModel->facilityRestrictBookers->count() == 0 
                            ?
                            'All teams in this area are Bookers'
                            :
                            $facilityModel->facilityRestrictBookers->pluck('schedulingTeamName')->implode(', ')
                            }}
                            @endif
                            </td>
                            <td>{{$facilityModel->provider_type}}</td>
                            <td>{{$facilityModel->FC_ProviderName}}</td>
                            <td>{{$facilityModel->facilityType->FT_FacilityType}}</td>
                            <td>{{$facilityModel->facilitySubTypes->implode('FST_FacilitySubType', ', ')}}</td>
                            <td class="primary-facility-td cursor-pointer" data-linked-facilities="{{e($facilityModel->linkedFacilities->select('FC_FacilityName', 'pivot')->toJson())}}">
                                {{$facilityModel->primary_facility ? 'Yes' : 'No'}}
                            </td>
                            <td>
                                {{ $facilityModel->current_location }}
                            </td>
                            <td>{{$facilityModel->default_booking_type}}</td>
                            <td>{{$facilityModel->FC_FacilityCapacity}}</td>
                            <td>{{ucfirst($facilityModel->FC_Accessible)}}</td>
                            <td>{{$facilityModel->facilityServices->pluck('SR_Service')->implode(', ')}}</td>
                            <td>{{$facilityModel->facilityEquipments->pluck('EQ_Equipment')->implode(', ')}}</td>
                            <td>{{($facilityAdministrator || $booker || $facilityAreaBelong) ? 'yes' : 'no'}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>            
        </div>
    </div>
</div>
<div id="facility-archive-date-modal">
</div>
<div id="facility-form-dialog">
</div>
<div id="facility-administrator-form-dialog">
</div>
@endsection
@push('scripts')
<script src="/mvc-app/public/{{mix('js/facility/facility.js')}}" type="text/javascript"></script>
<script src="/mvc-app/public/{{mix('js/facility/facility-filter.js')}}" type="text/javascript"></script>
<script>
    $(function() {
        new Facility({
            'createUrl' : '{{route('facility.create')}}',
            'storeUrl' : '{{route('facility.store')}}',
            'editUrl' : '{{route('facility.edit', ['facility' => ':facility'])}}',
            'updateUrl' : '{{route('facility.update', ['facility' => ':facility'])}}',
            'facilityBookersUrl': '{{route('facility.bookers', ['area' => ':area'])}}',
            'facilityShowUrl' : '{{route('facility.show', [':facility'])}}',
            'deleteFormUrl' : '{{route('facility.deleteForm', [':facility'])}}',
            'deleteUrl' : '{{route('facility.destroy', [':facility'])}}',
            'archiveFormUrl' : '{{route('facility.archiveForm', [':facility'])}}',
            'archiveUrl' : '{{route('facility.archive', [':facility'])}}',
            'facilityHistoryUrl': '{{route('facility.history', [':facility'])}}',
            'futureBookingCountUrl': '{{ route('facility.future-booking-count', ['facility' => ':facility']) }}',
            'facilityAdministratorUrl': '{{route('facility.facility-administrator', [':facility'])}}',
            'facilityAdministratorStoreUrl': '{{route('facility.facility-administrator-store', [':facility'])}}'
        });
        new FacilityFilter({
            filterAreaDataUrl : '{{route('facilityAreaList')}}',
            filterTypeDataUrl : '{{route('facilityTypeList')}}',
            filterSubTypeDataUrl : '{{route('facilitySubTypeList')}}',
            filterServiceDataUrl : '{{route('facilityServiceList')}}',
            filterEquipmentDataUrl : '{{route('facilityEquipmentList')}}',
            filterSaveUrl : '{{route('save.filter')}}',
            listSavedFilterUrl :  '{{route('list.filter')}}',
            deleteSavedFilterUrl :  '{{route('delete.filter', [':filter'])}}',
        });
    });
</script>
@endpush