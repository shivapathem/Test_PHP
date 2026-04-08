@extends('layouts.mail-layout')

@section('content')
    Dear {{ $user->UD_DisplayName }},
    <br>
    This is to inform you that your Facility Booking request has been <strong>declined</strong> due to facility unavailability.
    <br>

    @if(!empty($reason))
        Reason – {{ $reason }}<br>
    @endif

    Here are the details –
    <ul>
        <li>Booking Title - {{$facilityBooking->FB_BookingTitle}}</li>
        <li>
            {{ $facilityBooking->linkedToFacilityBooking ? 'Linked ' : '' }}Facility Name –
            {{ optional($facilityBooking->facility)->FC_FacilityName }}
        </li>
        
        {{-- Affected instances section (optional) --}}
        @if(!empty($bookings) && $bookings instanceof \Illuminate\Support\Collection && $bookings->count() > 1)
            <br>
            <strong>Affected instances (this operation):</strong>
            <ul>
                @foreach($bookings as $b)
                    @php
                        $start   = optional($b->FB_BookingStartDateTime);
                        $end     = optional($b->FB_BookingEndDateTime);
                        $fac     = optional($b->facility);
                        $parent  = optional($b->linkedToFacilityBooking);
                        $isLinked = !empty($b->linkedToFacilityBooking);
                    @endphp
                    <li>
                        {{-- Date & time --}}
                        {{ $start ? $start->format('d/m/Y') : '' }}
                        • {{ $start ? $start->format('H:i') : '' }}–{{ $end ? $end->format('H:i') : '' }}

                        {{-- Facility name --}}
                        • Facility: {{ $fac->FC_FacilityName }}

                        {{-- Linked role (mandatory/non‑mandatory flag omitted for simplicity; available if you want to pass it) --}}
                        @if($isLinked)
                            • (Linked to {{ optional($parent->facility)->FC_FacilityName }})
                        @else
                            • (Primary)
                        @endif

                        {{-- Location --}}
                        • Location: {{ $fac->current_location ?? optional($fac->internalLocation)->LN_Location }}
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($facilityBooking->linkedToFacilityBooking)
            <li>
                Primary Facility –
                {{ optional(optional($facilityBooking->linkedToFacilityBooking)->facility)->FC_FacilityName }}
            </li>
        @endif

        <li>Location – {{ optional($facilityBooking->facility)->current_location }}</li>

        {{-- List of all declined instance dates in this operation --}}
        <li>Date – {{ implode(', ', $declinedDates->unique()->toArray()) }}</li>

        {{-- Use the representative booking's times --}}
        <li>
            Start Time – {{ $facilityBooking->FB_BookingStartDateTime->format('H:i') }}
            &nbsp; End Time – {{ $facilityBooking->FB_BookingEndDateTime->format('H:i') }}
        </li>

        @if (!empty($facilityBooking->FB_RequestorNote))
            <li>Requestor Notes – {{ $facilityBooking->FB_RequestorNote }}</li>
        @endif

        @if (!empty($facilityBooking->FB_SchedulerNote))
            <li>Scheduler Notes – {{ $facilityBooking->FB_SchedulerNote }}</li>
        @endif

        @if (!empty(optional($facilityBooking->facility)->FC_FacilityNote))
            <li>Facility Notes – {{ optional($facilityBooking->facility)->FC_FacilityNote }}</li>
        @endif

        @if (!empty(optional($facilityBooking->facility)->FC_LocationNote))
            <li>Location Notes – {{ optional($facilityBooking->facility)->FC_LocationNote }}</li>
        @endif

        @if (!empty(optional($facilityBooking->facility)->FC_AccessibilityNote))
            <li>Accessibility Notes – {{ optional($facilityBooking->facility)->FC_AccessibilityNote }}</li>
        @endif

        @if (!empty(optional($facilityBooking->facility)->FC_PopupNote))
            <li>Pop‑up Notes – {{ optional($facilityBooking->facility)->FC_PopupNote }}</li>
        @endif
    </ul>
@endsection
