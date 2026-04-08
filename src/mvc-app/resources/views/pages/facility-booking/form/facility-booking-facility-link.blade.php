@php($facilityLink = $facility->linkedFacilities)
@php($existingLinkedFacilities = isset($facilityBooking) ? $facilityBooking->linkedFacilityBookings()->with('facility')->get()->pluck('facility') : collect([]))
<div id="facility-booking-facility-link-modal">
    <form id="facility-booking-facility-link-form">
        @if($facilityLink->count() == 0)
        <p>There are no linked facilities available.</p>
        @endif
        <table>
            <tbody id="facility-booking-facility-link-details-table-body">
                @foreach ($facilityLink ?? [] as $facilityLinkData)
                    <tr>
                        <td style="text-align:center">
                            <input data-facility-name="{{$facilityLinkData->FC_FacilityName}}"  type="checkbox" name="facility_booking_facility_link_mandatory[]" value="{{$facilityLinkData->FC_FacilityID}}"
                            @if($facilityLinkData->getOriginal('pivot_FCLK_Mandatory') == 1)
                                checked disabled
                            @elseif($existingLinkedFacilities->where('FC_FacilityID', $facilityLinkData->FC_FacilityID)->count() > 0)
                                checked
                            @endif
                            >
                        </td>
                        <td><span>{{$facilityLinkData->FC_FacilityName}}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
        <div style="text-align: right">
            <button type="button" style="font-size:12px" id="facility-booking-facility-link-save-btn" class="ui-button ui-widget ui-corner-all">Done</button>
        </div>
    </form>
</div>