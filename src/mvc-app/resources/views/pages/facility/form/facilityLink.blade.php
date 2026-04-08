@php($existingFacilityLink = isset($editFacility) ? $editFacility->linkedFacilities : null)
<div id="facility-link-modal" style="overflow: visible !important; position: relative;">
    <form id="facility-link-form" style="overflow: visible;">
        <table class="facility-form" style="overflow: visible;">
            <tbody>
                <tr style="overflow: visible;">
                    <td>
                        Select Facility type
                    </td>
                    <td style="overflow: visible;">
                        <select style="position: relative; z-index: 9999;" class="facility-form-input" data-placeholder="Select Facility" id="facility_link_chose" name="facility_link_chose[]" multiple>
                        <option></option>
                        @php($existingFacilityLinkArray = isset($existingFacilityLink) ? $existingFacilityLink->pluck('FC_FacilityName', 'FC_FacilityID')->toArray() : [])
                
                            @foreach ($facilityList->where('FC_DefaultBookingType',\App\Models\Facility\Facility::DEFAULT_BOOKING_SELF_BOOKED) as $facilityModel)
                                {{-- Skip current facility --}}
                                @if(isset($editFacility) && $editFacility->FC_FacilityID == $facilityModel->FC_FacilityID)
                                    @continue
                                @endif
                                 {{-- Hide primary facilities --}}
                                @if($facilityModel->primary_facility == 1)
                                    @continue
                                @endif
                                {{-- Hide facilities that would be disabled --}}
                                @if($facilityModel->belongsToLinkedFacilitiesAsMandatory->count() > 0 && !isset($existingFacilityLinkArray[$facilityModel->FC_FacilityID]))
                                    @continue
                                @endif                              

                                <option value="{{ $facilityModel->FC_FacilityID }}" data-facility-area="{{ $facilityModel->FC_AreaOwnerID }}" data-facility-linked="0" @if(isset($existingFacilityLinkArray[$facilityModel->FC_FacilityID])) selected @endif>
                                    {{ $facilityModel->FC_FacilityName }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="facility-form">
            <thead>
                <tr>
                    <th>
                        Details
                    </th>
                    <th>
                        Mandatory
                    </th>
                </tr>
            </thead>
            <tbody id="facility-link-details-table-body">
                {{--Below html content is also generted in js--}}
                @foreach ($existingFacilityLink ?? [] as $existingFacilityLinkData)
                    <tr>
                        <td><span>{{$existingFacilityLinkData->FC_FacilityName}}</span></td>
                        <td style="text-align:center">
                            <input type="checkbox" name="facility_link_mandatory[]" value="{{$existingFacilityLinkData->FC_FacilityID}}"
                            @if($existingFacilityLinkData->getOriginal('pivot_FCLK_Mandatory') == 1) checked @endif
                            >
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
        <div style="text-align: right">
            <button type="button" style="font-size:12px" id="facility-link-save-btn" class="ui-button ui-widget ui-corner-all">Done</button>
        </div>
    </form>
</div>