<div id="location-external-international-modal">
    @php($locationExternalInternational = isset($editFacility) && $editFacility->FC_ProviderType == 'external_international' ? $editFacility->location : null)

    <link rel="stylesheet" href="/mvc-app/public/{{mix('css/locationExternalInternational/locationExternalInternational.css')}}" />
    <form id="external-international-location-form">
        <div class="facility-form">
            <div class="facility-form__body">
                <div class="facility-form__row">
                    <div class="facility-form__cell">
                        Add complete address here
                    </div>
                </div>
                <div class="facility-form__row">
                    <div class="facility-form__cell">
                        <textarea id="location_external_international_address" name="location_external_international_address" style="width: 98%;height: 50px;">{{$locationExternalInternational->FCL_CompleteAddress_EXTINT ?? ''}}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div style="text-align: right">
            <button type="button" style="font-size:12px" id="facility-location-external-international-save-btn" class="ui-button ui-widget ui-corner-all">Done</button>
        </div>
    </form>
</div>