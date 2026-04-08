@extends('layouts.default')
@section('content')
<div id="page-container">
    <div id="filter-status"
     role="status"
     aria-live="polite"
     aria-atomic="true"
     class="acc-only" ></div>
    <button class="ui-button ui-widget ui-corner-all addbtn" id="create-action-button">
        <img src="/mvc-app/public/images/button_add.png" alt="">&nbsp; Add Actions
    </button>
                    <div class="tableheadersmall medtextboldcentre screen-fix-withscroll list-view" >
                        <h1 style="font-size: 11px; padding-top: 10px; margin: 0px;">Actions</h1>
                        <p style="padding-top: 2px;">Displaying a list of all Actions.</p>
                    </div>                 
                    <table id="action-list-table" class="display tablesmall">
                        <thead>
                            <tr>
                                <th scope="col">Actions</th>
                                <th scope="col">Action Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Created At</th>
                                <th scope="col">Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($actions as $action)
                                <tr>
                                    <td style="text-align: center">
                                        <i class="fas fa-edit edit-action-icon" data-action-id="{{$action->action_id}}" role="button" aria-label="Edit action" tabindex="0"></i>
                                        <i class="fas fa-trash-alt delete-action-icon" data-action-id="{{$action->action_id}}" role="button" aria-label="Delete action" tabindex="0"></i>
                                    </td>
                                    {{-- Changing td to th --}}
                                    <th scope="row" style="background: none;">{{ $action->action_name }}</th>
                                    <td>{{ Str::limit($action->description, 100) }}</td>
                                    <td>{{ $action->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $action->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
</div>
<div id="create-action">
    </div>
@endsection
@push('scripts')
<script src="/mvc-app/public/{{mix('js/facility/action.js')}}" type="text/javascript"></script>
<script>
    $(function() {
        new ActionVar({
            'createUrl' : '{{route('action.create')}}',
            'storeUrl' : '{{route('action.store')}}',
            'editUrl' : '{{route('action.edit', [':action'])}}',
            'updateUrl' : '{{route('action.update', [':action'])}}',
            'deleteFormUrl' : '{{route('action.deleteForm', [':action'])}}',
            'deleteUrl' : '{{route('action.destroy', [':action'])}}',
        });
    });
</script>
@endpush
