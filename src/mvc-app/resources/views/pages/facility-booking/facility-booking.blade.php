@extends('layouts.default')
@section('content')
<link rel="stylesheet" href="/mvc-app/public/{{mix('css/booking/facility-booking.css')}}" />

<div id="booking-weekly-view-container">

    
<div id="booking-toolbar" style="display: flex; padding: 5px; align-items: center; position: relative;">
    
    <!-- LEFT: make Days + Filter sit on the same line -->
    <div style="display: flex; align-items: center; gap: 10px; position: relative; z-index: 1; white-space: nowrap;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <label style="margin: 0;">Days</label>
            <select id="view-days" class="facility-booking-days" aria-label="Select the number of days" role="listbox" aria-live="polite">
                <option value="1">1</option>
                <option value="7">7</option>
            </select>
        </div>

        <div class="facility-booking-filter-div-container" style="display: inline-flex; align-items: center;">
            @include('pages.facility-booking.facility-booking-filter')
        </div>
    </div>

    <!-- CENTER: keep title truly centered, but behind other controls -->
    <h1 style="position: absolute; left: 50%; transform: translateX(-50%); text-align: center; font-size: 18px; margin: 0; z-index: 0; pointer-events: none;">
        Facility Bookings
    </h1>

    <!-- RIGHT: prevent wrapping and force calendar icon to render inline -->
    <div style="margin-left: auto; position: relative; z-index: 1;">
        <div style="display: flex; align-items: center; flex-wrap: nowrap; white-space: nowrap;">
            <button id="previous-day-btn" class="cursor-pointer" style="padding: 4px; margin-right: 12px; border: none;" >
                    <i class="fa fa-angle-double-left" aria-hidden="true"></i>
                    <span id="previous-text">Prev Day</span>
            </button>

            <button id="view-date-calender-icon" style="display: flex; align-items: center; z-index: 5000; border: none; padding: 0px;" >
                <input type="hidden" id="view-date-calender" value="">
                <img
                     src="{{ asset('images/calendar.gif') }}"
                     alt="Calendar"
                     title="Calendar"
                     style="width: 25px; height: 25px;"
                    >
            </button>

            <button id="next-day-btn" class="cursor-pointer" style="padding: 4px; margin-left: 15px; border: none;" >
                    <span id="next-text">Next Day</span>
                    <i class="fa fa-angle-double-right" ></i>
            </button>
            
        </div>
    </div>

</div>

<div style="display: flex; padding: 5px; align-items: center; position: relative; background-color: #ff7f50;">
    
    <div id="date-label-section" style="display: flex; align-items: center; justify-content: center; position: relative; z-index: 1; white-space: nowrap; width: 260px; min-width: 260px;">
        <span style="text-align: center; font-size: 12px; margin: 0; z-index: 0; pointer-events: none; width: 100%; font-weight: 600;">
            Date
        </span>
    </div>

    <div id="date-range-section" style="position: absolute; left: 50%; transform: translateX(-50%); text-align: center; font-size: 16px; margin: 0; z-index: 0; pointer-events: none; font-weight: 600;">
    </div>

</div>
    
<div id="weekly-view-time-line-container">
    <div>
</div>
<div id="facility-booking-form-dialog">
</div>
<div id="facility-booker-note-form-dialog">
</div>
<div id="facility-booking-cancel">
    @include('pages.facility-booking.form.facility-booking-cancellation')
    @include('pages.facility-booking.form.facility-booking-deletion')
</div>
<div id="facility-booking-reinstate-modal">
    <div id="facility-booking-reinstate-container">
    </div> 
</div>
<div id="delete-scheduler-note-modal">
    @include('pages.facility-booking.form.facility-booker-note-delete-form')
</div>
@endsection
@push('scripts')
<script src="/mvc-app/public/{{mix('js/booking/facility-booking.js')}}" type="text/javascript"></script>
<script src="/mvc-app/public/{{mix('js/booking/facility-booking-filter.js')}}" type="text/javascript"></script>
<script src="/mvc-app/public/{{mix('js/booking/facility-booking-websocket.js')}}" type="text/javascript"></script>
<script>
    let facilityBookingToken = "{{csrf_token()}}";
    var facilityBooking = new FacilityBooking({
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
        'copyFacilityBookingFormUrl': '{{route('facility-booking.copy', ['facilityBooking' => ':facilityBooking'])}}',
        'moveFacilityBookingFormUrl': '{{route('facility-booking.move-form', ['facilityBooking' => ':facilityBooking'])}}',
        'moveFacilityBookingUrl': '{{route('facility-booking.move', ['facility' => ':facility', 'facilityBooking' => ':facilityBooking'])}}',
        'historyFacilityBookingDataUrl' : '{{route('facility-booking.history', ['facilityBooking' => ':facilityBooking'])}}',
        'reinstateFacilityBookingDataUrl' : '{{route('facility-booking.reinstate_data', ['facilityBooking' => ':facilityBooking'])}}',
        'reinstateFacilityBookingPopUpUrl' : '{{route('facility-booking.reinstate_popup', ['facilityBooking' => ':facilityBooking'])}}',
        'showFacilityBookingUrl' : '{{route('facility-booking.show', ['facilityBooking' => ':facilityBooking'])}}',
        'createBookerNoteFormUrl' : '{{route('facility-booker-note.create', ['facility' => ':facility'])}}',
        'storeBookerNoteUrl' : '{{route('facility-booker-note.store', ['facility' => ':facility'])}}',
        'historySchedulerNoteDataUrl' : '{{route('facility-booker-note.history', ['facilityBookerNote' => ':facilityBookerNote'])}}',
        'editBookerNoteFormUrl' : '{{route('facility-booker-note.edit', ['facilityBookerNote' => ':facilityBookerNote'])}}',
        'updateBookerNoteUrl' : '{{route('facility-booker-note.update', ['facilityBookerNote' => ':facilityBookerNote'])}}',
        'deleteFacilityBookingUrl' : '{{route('facility-booking.delete', ['facilityBooking' => ':facilityBooking'])}}',
        'schedulerNoteDeleteUrl': '{{route('facility-booker-note.delete', ['facilityBookerNote' => ':facilityBookerNote'])}}',
        'deleteUrl' : '{{route('facility-booking.destroy', ['facilityBooking' => ':facilityBooking'])}}',
        'recurrenceAvailableCountUrl': '{{route('facilityBooking.getAvailableRecurrenceCount', ['facilityBooking' => ':facilityBookingId'])}}',
        'token' : facilityBookingToken,
        'constants' : {
            'self_booked_facility' : '{{App\Models\Facility\Facility::DEFAULT_BOOKING_SELF_BOOKED}}', 
            'managed_facility' : '{{App\Models\Facility\Facility::DEFAULT_BOOKING_MANAGED}}'
        },
        'userSetting' : {
            userAreaSettings: @json(auth()->user()->getAreasRoles->toArray()),
            facilitySpoofAdminRole: @json(auth()->user()->getFacilityAdministratorRole->toArray()),
            userTeamSettings: @json(auth()->user()->userSetup),
            userId: {{auth()->user()->UD_UserID}}
        }
    });
    var facilityBookingFilterInstance = new FacilityBookingFilter({
        filterTypeDataUrl : '{{route('facilityTypeList')}}',
        filterSubTypeDataUrl : '{{route('facilitySubTypeList')}}',
        filterServiceDataUrl : '{{route('facilityServiceList')}}',
        filterEquipmentDataUrl : '{{route('facilityEquipmentList')}}',
        filterSaveUrl : '{{route('save.filter')}}',
        listSavedFilterUrl :  '{{route('list.filter')}}',
        deleteSavedFilterUrl :  '{{route('delete.filter', [':filter'])}}',
        'getFacilityBookingsUrl' : '{{route('facilityBooking.getFacilityBookings')}}',
        'facilityBookingInstance' : facilityBooking,
        'token' : facilityBookingToken
    });
    facilityBooking.facilityBookingFilterInstance = facilityBookingFilterInstance;
    FacilityBookingWebSocket.facilityBookingFilterInstance = facilityBookingFilterInstance;
    FacilityBookingWebSocket.facilityBookingInstance = facilityBooking;
    FacilityBookingWebSocket.pusherScheme = '{{config('broadcasting.connections.reverb.options.scheme')}}';
    FacilityBookingWebSocket.pusherPort = '{{config('broadcasting.connections.reverb.options.port')}}';
    FacilityBookingWebSocket.init();
</script>
@endpush
