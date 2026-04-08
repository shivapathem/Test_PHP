<link rel="stylesheet" href="/mvc-app/public/{{mix('css/facilityType/facilityType.css')}}" />
<div id="facility-type-modal">
    @php($existingFacilitySubTypes = isset($editFacility) ? $editFacility->facilitySubTypes->pluck('FST_FacilitySubTypeID')->toArray() : [])
    @php($existingPrimaryFacilitySubType = isset($editFacility) ? $editFacility->facilitySubTypes->filter(function ($facilitySubTypeFilter) {
    return $facilitySubTypeFilter->getOriginal('pivot_FCST_PrimarySubType') == 1;
    })->first()->FST_FacilitySubTypeID : null)
    <form id="facility-type-form">
        <div class="plain-table facility-form">
            <div class="plain-table__row plain-table__row--header">
                <div class="plain-table__cell">Facility Type</div>
                <div class="plain-table__cell">Facility Sub Types</div>
                <div class="plain-table__cell">Mark Primary Sub Type</div>
            </div>
            <div class="plain-table__row plain-table__row--last">
                <div class="plain-table__cell">
                    @foreach($facilityTypeList as $facilityType)
                    <div class="plain-table__item">
                        <input type="radio" id="facility_type_frm_{{$facilityType->FT_FacilityTypeID}}" name="facility_type_frm" value="{{$facilityType->FT_FacilityTypeID}}"
                            @if(isset($editFacility) && $editFacility->FC_FacilityTypeID == $facilityType->FT_FacilityTypeID) checked @endif
                        >
                        <label for="facility_type_frm_{{$facilityType->FT_FacilityTypeID}}">{{$facilityType->FT_FacilityType}}</label>
                    </div>
                    @endforeach
                </div>

                <div class="plain-table__cell">
                    @foreach($facilityTypeList as $facilityType)
                    <div class="facility-sub-type-set" id="facility-sub-type-set-{{$facilityType->FT_FacilityTypeID}}" style="display: none">
                        @foreach($facilityType->facilitySubType as $facilitySubType)
                        <div class="plain-table__item">
                            <input type="checkbox" id="facility_sub_type_frm_{{$facilitySubType->FST_FacilitySubTypeID}}" name="facility_sub_type_frm[]" value="{{$facilitySubType->FST_FacilitySubTypeID}}"
                                @if(in_array($facilitySubType->FST_FacilitySubTypeID, $existingFacilitySubTypes)) checked @endif
                            >
                            <label for="facility_sub_type_frm_{{$facilitySubType->FST_FacilitySubTypeID}}">{{$facilitySubType->FST_FacilitySubType}}</label>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>

                <div class="plain-table__cell" style="align-items: center;">
                    @foreach($facilityTypeList as $facilityType)
                    <div class="facility-sub-type-primary-set" id="facility-sub-type-primary-set-{{$facilityType->FT_FacilityTypeID}}" style="display: none">
                        @foreach($facilityType->facilitySubType as $facilitySubType)
                        <div class="plain-table__item">
                            <input type="radio" id="facility_sub_type_primary_frm_{{$facilitySubType->FST_FacilitySubTypeID}}" name="facility_sub_type_primary_frm" value="{{$facilitySubType->FST_FacilitySubTypeID}}"
                                @if($existingPrimaryFacilitySubType==$facilitySubType->FST_FacilitySubTypeID) checked @elseif(!in_array($facilitySubType->FST_FacilitySubTypeID, $existingFacilitySubTypes)) disabled="disabled" @endif
                            >
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </form>
    <br>
    <div style="text-align: right">
        <button type="button" style="font-size:12px" id="facility-type-save-btn" class="ui-button ui-widget ui-corner-all">Done</button>
    </div>
</div>