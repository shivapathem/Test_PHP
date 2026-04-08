@extends('layouts.mail-layout')
@section('content')
    Dear {{$user->UD_DisplayName}},
    <br>
    <br>
    This is to inform you that your Facility Booking request has been submitted
    successfully.
    @if($facilityBookingRecurrence->facility->FC_DefaultBookingType == App\Models\Facility\Facility::DEFAULT_BOOKING_MANAGED)
    It has not yet been confirmed.You will be contacted again when a decision has been made.
    @endif
    <br>
    <br>
    Here are the details -
    <ul>
        <li>Booking Title - {{$facilityBookingRecurrence->facilityBookings->first()->FB_BookingTitle}}</li>
        <li>Facility Name - {{$facilityBookingRecurrence->facility->FC_FacilityName}}</li>
        <li>Location - {{$facilityBookingRecurrence->facility->current_location}}</li>
        @if($facilityBookingRecurrence->linkedFacilities->count() > 0)
        <li>Linked Facilities - {{$facilityBookingRecurrence->linkedFacilities->pluck('FC_FacilityName')->unique()->implode(', ')}} </li>
        @endif
        @if($facilityBookingRecurrence->is_recurring)
        <li>Series Start Date - {{$facilityBookingRecurrence->FBR_SeriesStartDate->format('d/m/Y')}} Series End Date - {{$facilityBookingRecurrence->FBR_SeriesEndDate->format('d/m/Y')}}</li>
        @else
        <li>Start Date - {{$facilityBookingRecurrence->FBR_SeriesStartDate->format('d/m/Y')}} End Date - {{$facilityBookingRecurrence->FBR_SeriesEndDate->format('d/m/Y')}}</li>
        @endif
        <li>Start Time - {{substr($facilityBookingRecurrence->FBR_StartTime, 0, 5)}}  End Time - {{substr($facilityBookingRecurrence->FBR_EndTime, 0, 5)}} </li>
        @if($facilityBookingRecurrence->FBR_RequestorNote != null)
        <li>Requestor Notes - {{$facilityBookingRecurrence->FBR_RequestorNote}}</li>
        @endif
        @if($facilityBookingRecurrence->FBR_SchedulerNote != null)
        <li>Scheduler Notes - {{$facilityBookingRecurrence->FBR_SchedulerNote}}</li>
        @endif
        <li>Facility Notes - {{$facilityBookingRecurrence->facility->FC_FacilityNote}}</li>
        <li>Location Notes - {{$facilityBookingRecurrence->facility->FC_LocationNote}}</li>
        <li>Accessibility Notes - {{$facilityBookingRecurrence->facility->FC_AccessibilityNote}}</li>
        @if($facilityBookingRecurrence->facility->FC_PopupNote)
        <li>Pop-up Notes - {{$facilityBookingRecurrence->facility->FC_PopupNote}}</li>
        @endif
    </ul>
@endsection