@extends('layouts.default')
@section('content')
    <div id="page-container">
        <div id="filter-status"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        class="acc-only" ></div>
        <button class="ui-button ui-widget ui-corner-all addbtn" id="create-facility-type-button">
            <img src="/mvc-app/public/images/button_add.png" alt="">&nbsp;Add Facility Type
        </button>
        <div class="tableheadersmall medtextboldcentre screen-fix-withscroll  list-view">
            <h1 style="font-size: 11px; padding-top: 10px; margin: 0px;">Facility Types</h1>
            <p style="padding-top: 2px;">Displaying a list of all Facility Types.</p>
        </div>
        <table id="facility-type-list-table" class="display tablesmall">
            <thead>
                <tr>
                    <th scope="col" style="text-align: center">Action</th>
                    <th scope="col">Facility Type</th>
                    <th scope="col">Facility Sub Type</th>
                    <th scope="col">Created At</th>
                    <th scope="col">Updated At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($facilityTypeList as $facilityType)
                    <tr>
                        <td style="text-align: center">
                            <i class="fas fa-edit edit-facilitytype-icon" data-facilitytype-id="{{$facilityType->FT_FacilityTypeID}}" role="button" aria-label="Edit facility type" tabindex="0"></i>
                            <i class="fas fa-trash-alt delete-facilitytype-icon" data-facilitytype-id="{{$facilityType->FT_FacilityTypeID}}" role="button" aria-label="Delete facility type" tabindex="0"></i>
                        </td>
                        <th scope="row" style="background: none;">{{$facilityType->FT_FacilityType}}</th>
                        <td>{{$facilityType->facilitySubType->implode('FST_FacilitySubType', ', ')}}</td>
                        <td>{{$facilityType->FT_CreatedDate->format('d/m/Y H:i')}}</td>
                        <td>{{$facilityType->FT_UpdatedDate->format('d/m/Y H:i')}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div id="create-facility-type">
    </div>
@endsection
@push('scripts')
<script src="/mvc-app/public/{{mix('js/facility/facility-type.js')}}" type="text/javascript"></script>
<script>
    $(function() {
        new FacilityType({
            'createUrl' : '{{route('facility-type.create')}}',
            'storeUrl' : '{{route('facility-type.store')}}',       
            'editUrl' : '{{route('facility-type.edit', [':facility-type'])}}',
            'updateUrl' : '{{route('facility-type.update', [':facility-type'])}}',
            'deleteFormUrl' : '{{route('facility-type.deleteForm', [':facility-type'])}}',
            'deleteUrl' : '{{route('facility-type.destroy', [':facility-type'])}}',
        });
    });
</script>
@endpush

