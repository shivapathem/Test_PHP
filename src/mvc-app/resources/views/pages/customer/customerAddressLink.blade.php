<link rel="stylesheet" href="/mvc-app/public/{{mix('css/customer/customerAddressLink.css')}}" />
<div id="company-addres-link-modal">
    <ul>
        <li><a href="#local-address">Local</a></li>
        <li><a href="#international-address">International Address</a></li>
    </ul>

    <div id="local-address" style="padding: 10px;">
        <form id="customer-adddress-form-local" autocomplete="off">
            <div class="external-customer-form">
                <div class="ec-box">
                    {{-- Row 1 --}}
                    <div class="ec-row">
                        <div class="ec-item">
                            <label class="ec-label" for="ec_buildingnumber">
                                Building Number/Name<span class="required-asterisk">*</span>
                            </label>
                        </div>
                        <div class="ec-item">
                            <input type="text"
                                class="external-customer-form-input"
                                id="ec_buildingnumber"
                                name="ec_buildingnumber"
                                value="{{ isset($customer) ? $customer->EC_BuildingNumber  : '' }}"
                                required aria-required="true">
                        </div>

                        <div class="ec-item">
                            <label class="ec-label" for="ec_street">
                                Street<span class="required-asterisk">*</span>
                            </label>
                        </div>
                        <div class="ec-item">
                            <input type="text"
                                class="external-customer-form-input"
                                id="ec_street"
                                name="ec_street"
                                value="{{ isset($customer) ? $customer->EC_Street  : '' }}"
                                required aria-required="true">
                        </div>
                    </div>

                    {{-- Row 2 --}}
                    <div class="ec-row">
                        <div class="ec-item">
                            <label class="ec-label" for="ec_city">
                                City<span class="required-asterisk">*</span>
                            </label>
                        </div>
                        <div class="ec-item">
                            <input type="text"
                                class="external-customer-form-input"
                                id="ec_city"
                                name="ec_city"
                                value="{{ isset($customer) ? $customer->EC_City  : '' }}"
                                required aria-required="true">
                        </div>

                        <div class="ec-item">
                            <label class="ec-label" for="ec_county">County</label>
                        </div>
                        <div class="ec-item">
                            <input type="text"
                                class="external-customer-form-input"
                                id="ec_county"
                                name="ec_county"
                                value="{{ isset($customer) ? $customer->EC_County  : '' }}">
                        </div>
                    </div>

                    {{-- Row 3 --}}
                    <div class="ec-row">
                        <div class="ec-item">
                            <label class="ec-label" for="ec_postcode">
                                Post Code<span class="required-asterisk">*</span>
                            </label>
                        </div>
                        <div class="ec-item">
                            <input type="text"
                                class="external-customer-form-input"
                                id="ec_postcode"
                                name="ec_postcode"
                                value="{{ isset($customer) ? $customer->EC_PostCode  : '' }}"
                                required aria-required="true">
                        </div>

                        <div class="ec-item">
                            <label class="ec-label" for="ec_country">
                                Country<span class="required-asterisk">*</span>
                            </label>
                        </div>
                        <div class="ec-item">
                            <input type="text"
                                class="external-customer-form-input"
                                id="ec_country"
                                name="ec_country"
                                value="{{ isset($customer) ? $customer->EC_Country  : '' }}"
                                required aria-required="true">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div style="font-size:12px; text-align: right;">
            <button style="margin-top: 10px;background: #d0d0d0;" class="submit-address-form-button ui-button ui-widget ui-corner-all">Add Address</button>
        </div>
    </div>

    <div id="international-address" style="padding: 10px;">
        <form id="customer-adddress-form-international" autocomplete="off">
            <div class="external-customer-form">
                <div class="ec-box">
                    {{-- Row 1 --}}
                    <div class="ec-row">
                        <div class="ec-item">
                            <label class="ec-label" for="ec_internationaladdress">
                                International Address<span class="required-asterisk">*</span>
                            </label>
                        </div>
                        <div class="ec-item ec-item--stack ec-item--span-3">
                            <textarea maxlength="250" id="ec_internationaladdress" name="ec_internationaladdress" rows="6" style="width: 100%; box-sizing: border-box;" required aria-required="true">{{ isset($customer) ? $customer->EC_InternationalAddress  : '' }}</textarea>
                            <div id="ec_internationaladdress_char_count" style="margin-top: 5px; font-size: 12px;text-align: left;">250 characters remaining</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div style="font-size:12px; text-align: right; background-color: white;">
            <button style="font-size:12px; margin-top: 10px; background: #d0d0d0;" class="submit-address-form-button ui-button ui-widget ui-corner-all">Add Address</button>
        </div>
    </div>

</div>