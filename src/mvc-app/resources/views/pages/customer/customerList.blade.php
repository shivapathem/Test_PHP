@extends('layouts.default')
@section('content')
<div id="page-container">
    <div id="filter-status"
     role="status"
     aria-live="polite"
     aria-atomic="true"
     class="acc-only" ></div>
    <button class="ui-button ui-widget ui-corner-all addbtn" id="create-customer-button" style="margin-bottom: 10px;">
        <img src="/mvc-app/public/images/button_add.png" alt="">&nbsp;Add External Customer
    </button>
    <div class="tableheadersmall medtextboldcentre screen-fix-withscroll list-view">
        <h1 style="font-size: 11px; padding-top: 10px; margin: 0px;">Customer</h1>
        <p style="padding-top: 2px;">Displaying a list of all Customers.</p>
    </div>
    <table id="customer-list-table" class="display tablesmall">
            <thead>
                <tr>
                    <th style="vertical-align: baseline;">Action</th>
                    <th>Customer ID</th>
                    <th>Contact Name</th>
                    <th id="position-header">Position</th>
                    <th>Phone Number</th>
                    <th>Email Address</th>
                    <th>Company Name</th>
                    <th style="vertical-align: baseline;">Company Address</th>
                </tr>
            </thead>
            <tbody>
                    @foreach ($customerList as $customer)
                    <tr class="column-border">
                        <td style="text-align: center">
                            <i class="fas fa-edit edit-customer-icon" role="button" aria-label="Edit customer" tabindex="0" data-customer-id="{{$customer->EC_ExternalCustomerID}}"></i>
                            <i class="fas fa-trash-alt delete-customer-icon" role="button" aria-label="Delete customer" tabindex="0" data-customer-id="{{$customer->EC_ExternalCustomerID}}"></i>
                        </td>
                        <td style="text-align: left;">{{$customer->EC_ExternalCustomerID }}</td>
                        <td>{{$customer->EC_ContactName }}</td>
                        <td>{{$customer->EC_Position }}</td>
                        <td>{{$customer->EC_ContactNumber }}</td>
                        <td>{{$customer->EC_ContactEmail }}</td>
                        <td>{{$customer->EC_CompanyName}}</td>
                        <td>{{$customer->location}}</td>
                    </tr>
                    @endforeach


            </tbody>
        </table>
<div id="customer-form-dialog">
</div>
</div>
@endsection
@push('scripts')
<script src="/mvc-app/public/{{mix('js/customer/customer.js')}}" type="text/javascript"></script>
<script>
    $(function() {
        new customerVar({
            'createUrl' : '{{route('customer.create')}}',
            'storeUrl' : '{{route('customer.store')}}',
            'editUrl' : '{{route('customer.edit', [':customer'])}}',
            'updateUrl' : '{{route('customer.update', [':customer'])}}',
            'deleteFormUrl' : '{{route('customer.deleteForm', [':customer'])}}',
            'deleteUrl' : '{{route('customer.destroy', [':customer'])}}',
        });
    });
</script>
@endpush
