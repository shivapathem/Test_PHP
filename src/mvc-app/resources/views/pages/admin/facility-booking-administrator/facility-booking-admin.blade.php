@extends('layouts.default')
@section('content')
<link rel="stylesheet" href="/mvc-app/public/{{mix('css/booking/facility-booking.css')}}" />
@if(!auth()->user()->isFacilityBooker())
{{-- Check if user is a booker--}}
<div class="common-container-style"><p>Only Facility Booker and Facility Administrators Can Access this Page.</p></div>
@endsection
@else
<div style="background: #d0d0d0;">
    <h1 style="text-align: center; font-size: 18px; padding: 10px; margin: 0px;">Bookings Administration</h1>
</div>
<div id="company-admin-booking-tab">
    <ul class="facility-booking-tab" role="tablist" aria-label="Booking status tabs">
        <li data-list-type="short_notice"><a href="#short_notice" id="tab-short_notice">Short Notice</a></li>
        <li data-list-type="new"><a href="#new" id="tab-new">New</a></li>
        <li data-list-type="pending"><a href="#pending" id="tab-pending">Pending</a></li>
        <li data-list-type="declined"><a href="#declined" id="tab-declined">Declined</a></li>
        <li data-list-type="cancelled"><a href="#cancelled" id="tab-cancelled">Cancelled</a></li>
    </ul>
    <div id="filter-status"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        class="acc-only" ></div>
    <div id="loading-spinner" style="display: none; margin-top: 10px;">
        <div id="spinner-container">
            <div class="loader-spinner" style="margin-left: 49%;"></div>
            <span>Loading</span>
        </div>
    </div>
    <div id="short_notice" class="scrollable-x" tabindex="0">
    </div>
    <div id="new" class="scrollable-x" tabindex="0">
    </div>
    <div id="pending" class="scrollable-x" tabindex="0">
    </div>
    <div id="declined" class="scrollable-x" tabindex="0">
    </div>
    <div id="cancelled" class="scrollable-x" tabindex="0">
    </div>
</div>
<input type="hidden" id="current-tab-selected-facility-administrator">
<div id="booking-weekly-view-container">
</div>
@endsection
@push('scripts')
<script src="/mvc-app/public/{{mix('js/booking/facility-booking-admin.js')}}" type="text/javascript"></script>
<script src="/mvc-app/public/{{mix('js/booking/facility-booking.js')}}" type="text/javascript"></script>
<script>
    $(function() {
        let filterBookingToken = "{{csrf_token()}}";
        var filterBooking = new FacilityBooking({
            'createFacilityBookingRequestFormUrl' : '{{route('facility-booking.create', ['facility' => ':facility'])}}',
            'createFacilityBookingRecordFormUrl' : '{{route('facility-booking-record.create', ['facility' => ':facility'])}}',
            'getFacilityBookingsUrl' : '{{route('facilityBooking.getFacilityBookings')}}',
            'storeFacilityBookingRequestUrl' : '{{route('facility-booking.store', ['facility' => ':facility'])}}',
            'storeFacilityBookingRecordUrl' : '{{route('facility-booking-record.store', ['facility' => ':facility'])}}',
            'editFacilityBookingFormUrl' : '{{route('facility-booking.edit', ['facilityBooking' => ':facilityBooking'])}}',
            'editFacilityBookingUpdateUrl' : '{{route('facility-booking.update', ['facilityBooking' => ':facilityBooking'])}}',
            'cancelFacilityBookingPopUpUrl' : '{{route('facility-booking.cancel_popup', ['facilityBooking' => ':facilityBooking'])}}',
            'cancelFacilityBookingUrl' : '{{route('facility-booking.cancel', ['facilityBooking' => ':facilityBooking'])}}',
            'cancelFacilityBookingDataUrl' : '{{route('facility-booking.cancel_data', ['facilityBooking' => ':facilityBooking'])}}',
            'reinstateFacilityBookingDataUrl' : '{{route('facility-booking.reinstate_data', ['facilityBooking' => ':facilityBooking'])}}',
            'reinstateFacilityBookingPopUpUrl' : '{{route('facility-booking.reinstate_popup', ['facilityBooking' => ':facilityBooking'])}}',
            'showFacilityBookingUrl' : '{{route('facility-booking.show', ['facilityBooking' => ':facilityBooking'])}}',
            'token' : filterBookingToken,
            'administratorPage': 1,
            'constants' : {
                'self_booked_facility' : '{{App\Models\Facility\Facility::DEFAULT_BOOKING_SELF_BOOKED}}', 
                'managed_facility' : '{{App\Models\Facility\Facility::DEFAULT_BOOKING_MANAGED}}', 
            },
            'userSetting' : {
                userAreaSettings: @json(auth()->user()->getAreasRoles->toArray()),
                facilitySpoofAdminRole: @json(auth()->user()->getFacilityAdministratorRole->toArray()),
                userTeamSettings: @json(auth()->user()->userSetup),
                userId: {{auth()->user()->UD_UserID}}
            }
        });

        let filterBookingAdminInstance = new FacilityBookingAdmin({
            'filterBooking' : filterBooking,
            'getAdminTabDataUrl' : '{{route('facility-booking-admin.tab-data')}}',
            'facilityBookingPageUrl' : '{{route('facility-booking.index')}}'
        });
        filterBooking.filterBookingAdminInstance = filterBookingAdminInstance;
    });
</script>
@endpush
@endif
