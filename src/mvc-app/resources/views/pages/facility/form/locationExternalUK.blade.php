<div id="location-external-uk-modal">
    <link rel="stylesheet" href="/mvc-app/public/{{mix('css/locationExternalUK/locationExternalUK.css')}}" />
    @php($locationExternalUk = isset($editFacility) && $editFacility->FC_ProviderType == 'external_uk' ? $editFacility->location : null)
    <form id="external-uk-location-form">
        <div class="facility-form">
            <div class="tbody">
                <div class="tr">
                    <div class="td">
                        Building Number/Name
                    </div>
                    <div class="td colspan-2">
                        <input value="{{$locationExternalUk->FCL_BuildingNumberName_EXTUK ?? ''}}" id="location_external_uk_building_number" name="location_external_uk_building_number" autocomplete="off" type="text" style="width: 90%;">
                    </div>
                    <div class="td">
                        Street
                    </div>
                    <div class="td colspan-2">
                        <textarea id="location_external_uk_street" name="location_external_uk_street" autocomplete="off" type="text" style="width: 90%;">{{$locationExternalUk->FCL_Street_EXTUK ?? ''}}</textarea>
                    </div>
                </div>
                <div class="tr">
                    <div class="td">
                        City
                    </div>
                    <div class="td colspan-2">
                        <input value="{{$locationExternalUk->FCL_City_EXTUK ?? ''}}" id="location_external_uk_city" name="location_external_uk_city" autocomplete="off" type="text" style="width: 90%;">
                    </div>
                    <div class="td">
                        County
                    </div>
                    <div class="td colspan-2">
                        <input value="{{$locationExternalUk->FCL_County_EXTUK ?? ''}}" id="location_external_uk_county" name="location_external_uk_county" autocomplete="off" type="text" style="width: 90%;">
                    </div>
                </div>
                <div class="tr">
                    <div class="td">
                        Post Code
                    </div>
                    <div class="td colspan-2">
                        <input value="{{$locationExternalUk->FCL_Postcode_EXTUK ?? ''}}" id="location_external_uk_post_code" name="location_external_uk_post_code" autocomplete="off" type="text" style="width: 90%;">
                    </div>
                    <div class="td">
                        Country
                    </div>
                    <div class="td colspan-2">
                        <input value="{{$locationExternalUk->FCL_Country_EXTUK ?? ''}}" id="location_external_uk_country" name="location_external_uk_country" autocomplete="off" type="text" style="width: 90%;">
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div style="text-align: right">
            <button type="button" style="font-size:12px" id="facility-location-external-uk-save-btn" class="ui-button ui-widget ui-corner-all">Done</button>
        </div>
    </form>
</div>