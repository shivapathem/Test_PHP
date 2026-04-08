@extends('layouts.mail-layout')
@section('content')
        Dear {{$bookingCreatedByuser->UD_DisplayName}},
        <br>
        <br>
        This is to inform you that your Facility Booking Request has been updated.
        <br>
        <br>        
        Here are the new details -
        <ul>
            <li>Booking Title - {{$firstBooking->FB_BookingTitle}}</li>
            <li>Facility Name - {{$updatedBookings->pluck('facility')->pluck('FC_FacilityName')->unique()->implode(', ')}}</li>
            <li>Location - {{$updatedBookings->pluck('facility')->pluck('current_location')->unique()->implode(', ')}}</li>
            @if($updatedBookings->pluck('linkedFacilityBookings')->count() > 0)
            <li>Linked Facilities - {{$updatedBookings->pluck('linkedFacilityBookings.*.facility.FC_FacilityName')->flatten()->unique()->implode(', ')}} </li>
            @endif
            <li><b>Date - {{$updatedBookings->pluck('FB_BookingStartDateTime')->map(fn ($dt) => $dt?->format('d/m/Y'))->implode(', ')}}</b></li>
            <li><b>Start Time - {{$firstBooking->FB_BookingStartDateTime->format('H:i')}} End Time - {{$firstBooking->FB_BookingEndDateTime->format('H:i')}}</b></li>
            @if($firstBooking->facilityBookingRecurrence->is_recurring)
            <li>Recurrence - {{$firstBooking->facilityBookingRecurrence->RecurrenceSettingString}}</li>
            @endif
            <li>Booking Status - {{mb_ucfirst($firstBooking->FB_BookingStatus)}}</li>
            @if($firstBooking->FB_RequestorNote != null)
            <li>Requestor Notes - {{$firstBooking->FB_RequestorNote}}</li>
            @endif
            @if($firstBooking->FB_SchedulerNote != null)
            <li>Scheduler Notes - {{$firstBooking->FB_SchedulerNote}}</li>
            @endif
            @if($firstBooking->facility->FC_FacilityNotes)
            <li>Facility Notes - {{$firstBooking->facility->FC_FacilityNotes}}</li>
            @endif
            @if($firstBooking->facility->FC_LocationNotes)
            <li>Location Notes - {{$firstBooking->facility->FC_LocationNotes}}</li>
            @endif
            @if($firstBooking->facility->FC_AccessibilityNotes)
            <li>Accessibility Notes - {{$firstBooking->facility->FC_AccessibilityNotes}}</li>
            @endif
            @if($firstBooking->facility->FC_PopupNote)
            <li>Pop-up Notes - {{$firstBooking->facility->FC_PopupNote}}</li>
            @endif
        </ul>
@endsection
