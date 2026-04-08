@extends('layouts.default')
@section('content')
    <div id="page-container">
        <div id="filter-status"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        class="acc-only" ></div>
        <button class="ui-button ui-widget ui-corner-all addbtn" id="create-service-button">
            <img src="/mvc-app/public/images/button_add.png" alt="">&nbsp;Add Service
        </button>
        <div class="tableheadersmall medtextboldcentre screen-fix-withscroll list-view">
            <h1 style="font-size: 11px; padding-top: 10px; margin: 0px;">Service</h1>
            <p style="padding-top: 2px;">Displaying a list of all Services.</p>
        </div>
        <table id="service-list-table" class="display tablesmall">
            <thead>
                <tr>
                    <th style="text-align: center">Action</th>
                    <th scope="col">Service</th>
                    <th scope="col">Created At</th>
                    <th scope="col">Updated At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($serviceList as $serviceType)
                    <tr>
                        <td style="text-align: center">
                            <i class="fas fa-edit edit-service-icon" role="button" aria-label="Edit service" tabindex="0" data-service-id="{{$serviceType->SR_ServiceID}}"></i> 
                            <i class="fas fa-trash-alt delete-service-icon" role="button" aria-label="Delete service" tabindex="0" data-service-id="{{$serviceType->SR_ServiceID}}"></i>
                        </td>
                        <th scope="row" style="background: none;">{{$serviceType->SR_Service}}</th>
                        <td>{{$serviceType->SR_CreatedDate->format('d/m/Y H:s')}}</td>
                        <td>{{$serviceType->SR_UpdatedDate->format('d/m/Y H:s')}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div id="create-service">
    </div>
@endsection
@push('scripts')
<script src="/mvc-app/public/{{mix('js/facility/service.js')}}" type="text/javascript"></script>
<script>
    $(function() {
        new ServiceVar({
            'createUrl' : '{{route('service.create')}}',
            'storeUrl' : '{{route('service.store')}}',
            'editUrl' : '{{route('service.edit', [':service'])}}',
            'updateUrl' : '{{route('service.update', [':service'])}}',
            'deleteFormUrl' : '{{route('service.deleteForm', [':service'])}}',
            'deleteUrl' : '{{route('service.destroy', [':service'])}}',
        });
    });
</script>
@endpush

