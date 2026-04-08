
@if($tabType == 'short_notice')
    <table id="admin-booking-shortnotice-table" class="display tablesmall facility-booking-heading">
        <thead>
            <tr>
                <th scope="col">Facility Name</th>
                <th scope="col">Facility Provider</th>
                <th scope="col"><p style="margin-bottom: 2px;">Location</p></th>
                <th scope="col">Facility Sub Type</th>
                <th scope="col">Linked Facilities</th>
                <th scope="col"><p style="margin-bottom: 2px;">Booking Title</p></th>
                <th scope="col"><p style="margin-bottom: 2px;">Date</p></th>
                <th scope="col">Time</th>
                <th scope="col">Recur</th>
                <th scope="col">Requestor Name</th>
                <th scope="col">Company Name</th>
                <th scope="col">Customer Name</th>
                <th scope="col">When Requested</th> {{--  Will be hidden used for filter--}}
                <th scope="col">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facilityBookingList as $facilityBookingAdminList)
            @csrf
            <tr style="{{ \Carbon\Carbon::parse($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])->isPast() ? 'color:#A02B07;' : '' }}">
                <th style="{{ \Carbon\Carbon::parse($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])->isPast() ? 'color:#A02B07; background: none;' : '' }}">{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_FacilityName'] }}</th>
                <td>{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_ProviderName'] }}</td>
                <td>{{ $facilityBookingAdminList['location'] }}</td>
                <td>{{ $facilityBookingAdminList['FST_FacilitySubType'] }}</td>
                <td>@php $linked_facilities = $facilityBookingAdminList['linked_facility'] ?? [];@endphp
                    @if(!empty($linked_facilities)){{ implode(', ', $linked_facilities) }}@endif
                </td>
                <td class="update_booking_admin" data-booking-date="{{date('Y-m-d', strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime']))}}" data-linked-facility-booking="{{implode(',', $facilityBookingAdminList['linked_bookings'])}}" data-facility-booking-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityBookingID'] }}" data-facility-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityID'] }}" data-booking-type="{{ $facilityBookingAdminList['booking_type'] ?? '' }}"><a href="javascript:void(0)">{{ $facilityBookingAdminList['facility_bookings']['FB_BookingTitle'] }}</a></td>
                <td>{{ date('d/m/Y',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }}</td>
                <td style="width: 120px;">{{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }} - {{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingEndDateTime'])) }}</td>
                <td>{{ $facilityBookingAdminList['recur'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_RequestorName'] }}</td>
                <td>{{ $facilityBookingAdminList['external_customer'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_ContactName'] }}</td>
                <td>{{ date('d/m/Y H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_CreatedDate'])) }}</td>
                <td> Requestor : {{ $facilityBookingAdminList['facility_bookings']['FB_RequestorNote'] }} <br/> Scheduler : {{ $facilityBookingAdminList['facility_bookings']['FB_SchedulerNote'] }}</td>
            </tr>
            @endforeach


        </tbody>
    </table>
@endif
@if($tabType == 'new')
    <table id="admin-booking-new-table" class="display tablesmall facility-booking-heading">
        <thead>
            <tr>
                <th scope="col">Facility Name</th>
                <th scope="col">Facility Provider</th>
                <th scope="col"><p style="margin-bottom: 2px;">Location</p></th>
                <th scope="col">Facility Sub Type</th>
                <th scope="col">Linked Facilities</th>
                <th scope="col"><p style="margin-bottom: 2px;">Booking Title</p></th>
                <th scope="col"><p style="margin-bottom: 2px;">Date</p></th>
                <th scope="col">Time</th>
                <th scope="col">Recur</th>
                <th scope="col">Requestor Name</th>
                <th scope="col">Company Name</th>
                <th scope="col">Customer Name</th>
                <th scope="col">When Requested</th> {{--  Will be hidden used for filter--}}
                <th scope="col">Notes</th>
            </tr>
        </thead>
        <tbody>
            {{-- Previous Booking --}}
            @foreach ($facilityBookingListNewPrevious as $facilityBookingAdminList)
            <tr  style="color: #A02B07;">
                <th scope="row" style="background: none;color: #A02B07;">{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_FacilityName'] }}</th>
                <td>{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_ProviderName'] }}</td>
                <td>{{ $facilityBookingAdminList['location'] }}</td>
                <td>{{ $facilityBookingAdminList['FST_FacilitySubType'] }}</td>
                <td>@php $linked_facilities = $facilityBookingAdminList['linked_facility'] ?? [];@endphp
                    @if(!empty($linked_facilities)){{ implode(', ', $linked_facilities) }}@endif
                </td>
                <td class="update_booking_admin" data-booking-date="{{date('Y-m-d', strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime']))}}" data-linked-facility-booking="{{implode(',', $facilityBookingAdminList['linked_bookings'])}}" data-facility-booking-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityBookingID'] }}" data-facility-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityID'] }}" data-booking-type="{{ $facilityBookingAdminList['booking_type'] ?? '' }}"><a href="javascript:void(0)">{{ $facilityBookingAdminList['facility_bookings']['FB_BookingTitle'] }}</a></td>
                <td>{{ date('d/m/Y',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }}</td>
                <td style="width: 120px;">{{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }} - {{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingEndDateTime'])) }}</td>
                <td>{{ $facilityBookingAdminList['recur'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_RequestorName'] }}</td>
                <td>{{ $facilityBookingAdminList['external_customer'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_ContactName'] }}</td>
                <td>{{ date('d/m/Y H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_CreatedDate'])) }}</td>
                <td> Requestor : {{ $facilityBookingAdminList['facility_bookings']['FB_RequestorNote'] }} <br/> Scheduler : {{ $facilityBookingAdminList['facility_bookings']['FB_SchedulerNote'] }}</td>
            </tr>
            @endforeach

            {{-- Future Bookings --}}
            @foreach ($facilityBookingListNewFuture as $facilityBookingAdminList)
            <tr>
                <td>{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_FacilityName'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_ProviderName'] }}</td>
                <td>{{ $facilityBookingAdminList['location'] }}</td>
                <td>{{ $facilityBookingAdminList['FST_FacilitySubType'] }}</td>
                <td>@php $linked_facilities = $facilityBookingAdminList['linked_facility'] ?? [];@endphp
                    @if(!empty($linked_facilities)){{ implode(', ', $linked_facilities) }}@endif
                </td>
                <td class="update_booking_admin" data-booking-date="{{date('Y-m-d', strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime']))}}" data-linked-facility-booking="{{implode(',', $facilityBookingAdminList['linked_bookings'])}}" data-facility-booking-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityBookingID'] }}" data-facility-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityID'] }}" data-booking-type="{{ $facilityBookingAdminList['booking_type'] ?? '' }}"><a href="javascript:void(0)">{{ $facilityBookingAdminList['facility_bookings']['FB_BookingTitle'] }}</a></td>
                <td>{{ date('d/m/Y',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }}</td>
                <td style="width: 120px;">{{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }} - {{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingEndDateTime'])) }}</td>
                <td>{{ $facilityBookingAdminList['recur'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_RequestorName'] }}</td>
                <td>{{ $facilityBookingAdminList['external_customer'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_ContactName'] }}</td>
                <td>{{ date('d/m/Y H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_CreatedDate'])) }}</td>
                <td> Requestor : {{ $facilityBookingAdminList['facility_bookings']['FB_RequestorNote'] }} <br/> Scheduler : {{ $facilityBookingAdminList['facility_bookings']['FB_SchedulerNote'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@if($tabType == 'pending')
    <table id="admin-booking-pending-table" class="display tablesmall facility-booking-heading">
        <thead>
            <tr>
                <th scope="col">Facility Name</th>
                <th scope="col">Facility Provider</th>
                <th scope="col"><p style="margin-bottom: 2px;">Location</p></th>
                <th scope="col">Facility Sub Type</th>
                <th scope="col">Linked Facilities</th>
                <th scope="col"><p style="margin-bottom: 2px;">Booking Title</p></th>
                <th scope="col"><p style="margin-bottom: 2px;">Date</p></th>
                <th scope="col">Time</th>
                <th scope="col">Recur</th>
                <th scope="col">Requestor Name</th>
                <th scope="col">Company Name</th>
                <th scope="col">Customer Name</th>
                <th scope="col">When Requested</th> {{--  Will be hidden used for filter--}}
                <th scope="col">Notes</th>
            </tr>
        </thead>
        <tbody>

            {{-- Past Bookings --}}

            @foreach ($facilityBookingListPendingPrevious as $facilityBookingAdminList)
            <tr style="color: #A02B07;">
                @csrf
                <th scope="row" style="background: none;color: #A02B07;">{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_FacilityName'] }}</th>
                <td>{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_ProviderName'] }}</td>
                <td>{{ $facilityBookingAdminList['location'] }}</td>
                <td>{{ $facilityBookingAdminList['FST_FacilitySubType'] }}</td>
                <td>@php $linked_facilities = $facilityBookingAdminList['linked_facility'] ?? [];@endphp
                    @if(!empty($linked_facilities)){{ implode(', ', $linked_facilities) }}@endif
                </td>
                <td class="update_booking_admin" data-booking-date="{{date('Y-m-d', strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime']))}}" data-linked-facility-booking="{{implode(',', $facilityBookingAdminList['linked_bookings'])}}" data-facility-booking-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityBookingID'] }}" data-facility-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityID'] }}" data-booking-type="{{ $facilityBookingAdminList['booking_type'] ?? '' }}"><a href="javascript:void(0)">{{ $facilityBookingAdminList['facility_bookings']['FB_BookingTitle'] }}</a></td>
                <td>{{ date('d/m/Y',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }}</td>
                <td style="width: 120px;">{{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }} - {{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingEndDateTime'])) }}</td>
                <td>{{ $facilityBookingAdminList['recur'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_RequestorName'] }}</td>
                <td>{{ $facilityBookingAdminList['external_customer'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_ContactName'] }}</td>
                <td>{{ date('d/m/Y H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_CreatedDate'])) }}</td>
                <td> Requestor : {{ $facilityBookingAdminList['facility_bookings']['FB_RequestorNote'] }} <br/> Scheduler : {{ $facilityBookingAdminList['facility_bookings']['FB_SchedulerNote'] }}</td>
            </tr>
            @endforeach

            {{-- Future Bookings --}}
            @foreach ($facilityBookingListPendingFuture as $facilityBookingAdminList)
            <tr>
                <td>{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_FacilityName'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_ProviderName'] }}</td>
                <td>{{ $facilityBookingAdminList['location'] }}</td>
                <td>{{ $facilityBookingAdminList['FST_FacilitySubType'] }}</td>
                <td>@php $linked_facilities = $facilityBookingAdminList['linked_facility'] ?? [];@endphp
                    @if(!empty($linked_facilities)){{ implode(', ', $linked_facilities) }}@endif
                </td>
                <td class="update_booking_admin" data-booking-date="{{date('Y-m-d', strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime']))}}" data-linked-facility-booking="{{implode(',', $facilityBookingAdminList['linked_bookings'])}}" data-facility-booking-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityBookingID'] }}" data-facility-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityID'] }}" data-booking-type="{{ $facilityBookingAdminList['booking_type'] ?? '' }}"><a href="javascript:void(0)">{{ $facilityBookingAdminList['facility_bookings']['FB_BookingTitle'] }}</a></td>
                <td>{{ date('d/m/Y',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }}</td>
                <td style="width: 120px;">{{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }} - {{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingEndDateTime'])) }}</td>
                <td>{{ $facilityBookingAdminList['recur'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_RequestorName'] }}</td>
                <td>{{ $facilityBookingAdminList['external_customer'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_ContactName'] }}</td>
                <td>{{ date('d/m/Y H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_CreatedDate'])) }}</td>
                <td> Requestor : {{ $facilityBookingAdminList['facility_bookings']['FB_RequestorNote'] }} <br/> Scheduler : {{ $facilityBookingAdminList['facility_bookings']['FB_SchedulerNote'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@if($tabType == 'declined')
    <table id="admin-booking-declined-table" class="display tablesmall facility-booking-heading">
        <thead>
            <tr>
                <th scope="col">Facility Name</th>
                <th scope="col">Facility Provider</th>
                <th scope="col"><p style="margin-bottom: 2px;">Location</p></th>
                <th scope="col">Facility Sub Type</th>
                <th scope="col">Linked Facilities</th>
                <th scope="col"><p style="margin-bottom: 2px;">Booking Title</p></th>
                <th scope="col"><p style="margin-bottom: 2px;">Date</p></th>
                <th scope="col">Time</th>
                <th scope="col">Recur</th>
                <th scope="col">Requestor Name</th>
                <th scope="col">Company Name</th>
                <th scope="col">Customer Name</th>
                <th scope="col">When Requested</th> {{--  Will be hidden used for filter--}}
                <th scope="col">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facilityBookingListDeclined as $facilityBookingAdminList)
            @csrf
            <tr>
                <th scope="row" style="background: none;">{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_FacilityName'] }}</th>
                <td>{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_ProviderName'] }}</td>
                <td>{{ $facilityBookingAdminList['location'] }}</td>
                <td>{{ $facilityBookingAdminList['FST_FacilitySubType'] }}</td>
                <td>@php $linked_facilities = $facilityBookingAdminList['linked_facility'] ?? [];@endphp
                    @if(!empty($linked_facilities)){{ implode(', ', $linked_facilities) }}@endif
                </td>
                <td class="update_booking_admin" data-booking-date="{{date('Y-m-d', strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime']))}}" data-linked-facility-booking="{{implode(',', $facilityBookingAdminList['linked_bookings'])}}" data-facility-booking-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityBookingID'] }}" data-facility-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityID'] }}" data-booking-type="{{ $facilityBookingAdminList['booking_type'] ?? '' }}"><a href="javascript:void(0)">{{ $facilityBookingAdminList['facility_bookings']['FB_BookingTitle'] }}</a></td>
                <td>{{ date('d/m/Y',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }}</td>
                <td style="width: 120px;">{{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }} - {{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingEndDateTime'])) }}</td>
                <td>{{ $facilityBookingAdminList['recur'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_RequestorName'] }}</td>
                <td>{{ $facilityBookingAdminList['external_customer'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_ContactName'] }}</td>
                <td>{{ date('d/m/Y H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_CreatedDate'])) }}</td>
                <td> Requestor : {{ $facilityBookingAdminList['facility_bookings']['FB_RequestorNote'] }} <br/> Scheduler : {{ $facilityBookingAdminList['facility_bookings']['FB_SchedulerNote'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@if($tabType == 'cancelled')
    <table id="admin-booking-cancelled-table" class="display tablesmall facility-booking-heading">
        <thead>
            <tr>
                <th scope="col">Facility Name</th>
                <th scope="col">Facility Provider</th>
                <th scope="col"><p style="margin-bottom: 2px;">Location</p></th>
                <th scope="col">Facility Sub Type</th>
                <th scope="col">Linked Facilities</th>
                <th scope="col"><p style="margin-bottom: 2px;">Booking Title</p></th>
                <th scope="col"><p style="margin-bottom: 2px;">Date</p></th>
                <th scope="col">Time</th>
                <th scope="col">Recur</th>
                <th scope="col">Requestor Name</th>
                <th scope="col">Company Name</th>
                <th scope="col">Customer Name</th>
                <th scope="col">When Requested</th> {{--  Will be hidden used for filter--}}
                <th scope="col">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facilityBookingListCancelled as $facilityBookingAdminList)
            <tr>
                <th scope="row" style="background: none;">{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_FacilityName'] }}</th>
                <td>{{ $facilityBookingAdminList['facility_bookings']['facility']['FC_ProviderName'] }}</td>
                <td>{{ $facilityBookingAdminList['location'] }}</td>
                <td>{{ $facilityBookingAdminList['FST_FacilitySubType'] }}</td>
                <td>@php $linked_facilities = $facilityBookingAdminList['linked_facility'] ?? [];@endphp
                    @if(!empty($linked_facilities)){{ implode(', ', $linked_facilities) }}@endif
                </td>
                <td class="update_booking_admin" data-booking-date="{{date('Y-m-d', strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime']))}}" data-linked-facility-booking="{{implode(',', $facilityBookingAdminList['linked_bookings'])}}" data-facility-booking-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityBookingID'] }}" data-facility-id="{{ $facilityBookingAdminList['facility_bookings']['FB_FacilityID'] }}" data-booking-type="{{ $facilityBookingAdminList['booking_type'] ?? '' }}"><a href="javascript:void(0)">{{ $facilityBookingAdminList['facility_bookings']['FB_BookingTitle'] }}</a></td>
                <td>{{ date('d/m/Y',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }}</td>
                <td style="width: 120px;">{{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingStartDateTime'])) }} - {{ date('H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_BookingEndDateTime'])) }}</td>
                <td>{{ $facilityBookingAdminList['recur'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_RequestorName'] }}</td>
                <td>{{ $facilityBookingAdminList['external_customer'] }}</td>
                <td>{{ $facilityBookingAdminList['facility_bookings']['FB_ContactName'] }}</td>
                <td>{{ date('d/m/Y H:i',strtotime($facilityBookingAdminList['facility_bookings']['FB_CreatedDate'])) }}</td>
                <td> Requestor : {{ $facilityBookingAdminList['facility_bookings']['FB_RequestorNote'] }} <br/> Scheduler : {{ $facilityBookingAdminList['facility_bookings']['FB_SchedulerNote'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif