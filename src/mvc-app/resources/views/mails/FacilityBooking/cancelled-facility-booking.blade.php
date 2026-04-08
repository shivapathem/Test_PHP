@extends('layouts.mail-layout')
@section('content')
        Dear {{$user->UD_DisplayName}},
        <br>
        <br>
        This is to inform you that your Facility Booking request has been cancelled. 
        <br>
        <br>
        Here are the details -.
        <ul>
            <li>Booking Title - {{$facilityBooking->FB_BookingTitle}}</li>
            <li>{{$facilityBooking->linkedToFacilityBooking ? 'Linked ' : '' }}Facility Name - {{$facilityBooking->facility->FC_FacilityName}}</li>
            @if ($facilityBooking->linkedToFacilityBooking)
                <li>Primary Facility -{{$facilityBooking->linkedToFacilityBooking?->facility?->FC_FacilityName}}</li>
            @endif
            <li>Location - {{$facilityBooking->facility->current_location}}</li>
            <li>Date - {{implode(', ', $cancelledDates->unique()->toArray())}} </li>
            <li>Start Time - {{$facilityBooking->FB_BookingStartDateTime->format('H:i')}}  End Time - {{$facilityBooking->FB_BookingEndDateTime->format('H:i')}} </li>
            @if(isset($facilityBooking->FB_RequestorNote))
            <li>Requestor Notes - {{$facilityBooking->FB_RequestorNote}}</li>
            @endif
            @if(isset($facilityBooking->FB_SchedulerNote))
            <li>Scheduler Notes - {{$facilityBooking->FB_SchedulerNote}}</li>
            @endif
            @if(isset($facilityBooking->facility->FC_FacilityNote))
            <li>Facility Notes - {{$facilityBooking->facility->FC_FacilityNote}}</li>
            @endif
            @if(isset($facilityBooking->facility->FC_LocationNote))
            <li>Location Notes - {{$facilityBooking->facility->FC_LocationNote}}</li>
            @endif
            @if(isset($facilityBooking->facility->FC_AccessibilityNote))
            <li>Accessibility Notes - {{$facilityBooking->facility->FC_AccessibilityNote}}</li>
            @endif
            @if(isset($facilityBooking->facility->FC_PopupNote))
            <li>Pop-up Notes - {{$facilityBooking->facility->FC_PopupNote}}</li>
            @endif
        </ul>
@endsection