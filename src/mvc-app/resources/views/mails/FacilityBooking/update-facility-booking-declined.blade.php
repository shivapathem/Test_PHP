@extends('layouts.mail-layout')
@section('content')
        Dear {{$bookingCreatedByuser->UD_DisplayName}},
        <br>
        <br>
        This is to inform you that, unfortunately, your Facility Booking Request has been declined.
        <br>
        @if(!empty($firstBooking->FB_BookingDeclineReason))
        The following reason was given: <br>
        {{$firstBooking->FB_BookingDeclineReason}} <br>
        @endif
        Here are the details -
        <ul>
            <li>Booking Title - {{$firstBooking->FB_BookingTitle}}</li>
            <li>Facility Name - {{$updatedBookings->pluck('facility')->pluck('FC_FacilityName')->unique()->implode(', ')}}</li>
            <li>Location - {{$updatedBookings->pluck('facility')->pluck('current_location')->unique()->implode(', ')}}</li>
            @if($updatedBookings->pluck('linkedFacilityBookings')->count() > 0)
            <li>Linked Facilities - {{$updatedBookings->pluck('linkedFacilityBookings.*.facility.FC_FacilityName')->flatten()->unique()->implode(', ')}} </li>
            @endif
            <li>Date - {{$updatedBookings->pluck('FB_BookingStartDateTime')->map(fn ($dt) => $dt?->format('d/m/Y'))->implode(', ')}}</li>
            <li>Start Time - {{$firstBooking->FB_BookingStartDateTime->format('H:i')}} End Time - {{$firstBooking->FB_BookingEndDateTime->format('H:i')}}</li>
            @if($firstBooking->facilityBookingRecurrence->is_recurring)
            <li>Recurrence - {{$firstBooking->facilityBookingRecurrence->RecurrenceSettingString}}</li>
            @endif
            @if($firstBooking->FB_RequestorNote != null)
            <li>Requestor Notes - {{$firstBooking->FB_RequestorNote}}</li>
            @endif
            @if($firstBooking->FB_SchedulerNote != null)
            <li>Scheduler Notes - {{$firstBooking->FB_SchedulerNote}}</li>
            @endif
            @if($firstBooking->facility->FC_PopupNote)
            <li>Pop-up Notes - {{$firstBooking->facility->FC_PopupNote}}</li>
            @endif
        </ul>
@endsection
