@extends('layouts.default')
@section('content')
    <div id="page-container">
        <div id="filter-status"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        class="acc-only" ></div>
        <button class="ui-button ui-widget ui-corner-all addbtn" id="create-location-button">
            <img src="/mvc-app/public/images/button_add.png" alt="">&nbsp;Add Location
        </button>
        <div class="tableheadersmall medtextboldcentre screen-fix-withscroll list-view">
            <h1 style="font-size: 11px; padding-top: 10px; margin: 0px;">Location</h1>
            <p style="padding-top: 2px;">Displaying a list of all Locations.</p>
        </div>
        <table id="location-list-table" class="display tablesmall">
            <thead>
                <tr>
                    <th scope="col">Action</th>
                    <th scope="col">Location</th>
                    <th scope="col">Created At</th>
                    <th scope="col">Updated At</th>
                </tr>
            </thead>
        <tbody>
                @foreach ($locationList as $locationType)
                    <tr>
                        <td style="text-align: center">
                            <i class="fas fa-edit edit-location-icon" role="button" aria-label="Edit location" tabindex="0" data-location-id="{{$locationType->LN_LocationID}}"></i>
                            <i class="fas fa-trash-alt delete-location-icon" role="button" aria-label="Delete location" tabindex="0" data-location-id="{{$locationType->LN_LocationID}}"></i>
                        </td>
                        <th scope="row" style="background: none;">{{$locationType->LN_Location}}</th>
                        <td>{{$locationType->LN_CreatedDate->format('d/m/Y H:i')}}</td>
                        <td>{{$locationType->LN_UpdatedDate->format('d/m/Y H:i')}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div id="create-location">
    </div>
@endsection
@push('scripts')
<script src="/mvc-app/public/{{mix('js/facility/location.js')}}" type="text/javascript"></script>
<script>
    $(function() {
        new LocationVar({
            'createUrl' : '{{route('location.create')}}',
            'storeUrl' : '{{route('location.store')}}',
            'editUrl' : '{{route('location.edit', [':location'])}}',
            'updateUrl' : '{{route('location.update', [':location'])}}',
            'deleteFormUrl' : '{{route('location.deleteForm', [':location'])}}',
            'deleteUrl' : '{{route('location.destroy', [':location'])}}',
        });
    });
</script>
@endpush

