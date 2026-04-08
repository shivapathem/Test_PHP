@extends('layouts.default')
@section('content')
    <div id="page-container">
        <div id="filter-status"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        class="acc-only" ></div>
        <button class="ui-button ui-widget ui-corner-all addbtn" id="create-equipment-button">
                <img src="/mvc-app/public/images/button_add.png" alt="">&nbsp;Add Equipment
        </button>
        <div class="tableheadersmall medtextboldcentre screen-fix-withscroll list-view">
            <h1 style="font-size: 11px; padding-top: 10px; margin: 0px;">Equipment</h1>
            <p style="padding-top: 2px;">Displaying a list of all Equipments.</p>
        </div>
        <table id="equipment-list-table" class="display tablesmall">
            <thead>
                <tr>
                    <th scope="col" style="text-align: center">Action</th>
                    <th scope="col">Equipment</th>
                    <th scope="col">Equipment Type</th>
                    <th scope="col">Created At</th>
                    <th scope="col">Updated At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equipmentList as $equipmentType)
                    <tr>
                        <td style="text-align: center">
                            <i class="fas fa-edit edit-equipment-icon" data-equipment-id="{{$equipmentType->EQ_EquipmentID}}" role="button" aria-label="Edit equipment" tabindex="0"></i> 
                            <i class="fas fa-trash-alt delete-equipment-icon" data-equipment-id="{{$equipmentType->EQ_EquipmentID}}" role="button" aria-label="Delete equipment" tabindex="0"></i>
                        </td>
                        <th scope="row" style="background: none;">{{$equipmentType->EQ_Equipment}}</th>
                        <td>@if($equipmentType->EQ_Equipment_Type == "SW") Software @else Equipment @endif</td>
                        <td>{{$equipmentType->EQ_CreatedDate->format('d/m/Y H:s')}}</td>
                        <td>{{$equipmentType->EQ_UpdatedDate->format('d/m/Y H:s')}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div id="create-equipment">
    </div>
@endsection
@push('scripts')
<script src="/mvc-app/public/{{mix('js/facility/equipment.js')}}" type="text/javascript"></script>
<script>
    $(function() {
        new EquipmentVar({
            'createUrl' : '{{route('equipment.create')}}',
            'storeUrl' : '{{route('equipment.store')}}',
            'editUrl' : '{{route('equipment.edit', [':equipment'])}}',
            'updateUrl' : '{{route('equipment.update', [':equipment'])}}',
            'deleteFormUrl' : '{{route('equipment.deleteForm', [':equipment'])}}',
            'deleteUrl' : '{{route('equipment.destroy', [':equipment'])}}',
        });
    });
</script>
@endpush

