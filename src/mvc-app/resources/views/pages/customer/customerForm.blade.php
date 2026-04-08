<link rel="stylesheet" href="/mvc-app/public/{{mix('css/customer/customerForm.css')}}" />
<div class="external-customer-form">
    <!-- <h1 class="acc-only">Add External Customer</h1> -->
    <form id="create-customer-form" autocomplete="off">
        <div class="ec-box">
            {{-- Row 1 --}}
            <div class="ec-row ec-heading" aria-level="2">
                <h2 class="ec-item" style="flex: 0 0 100%;font-size: 12px; margin: 3px 0px;">Customer Details</h2>
            </div>

            {{-- Row 2 --}}
            <div class="ec-row">
                <div class="ec-item">
                    <label class="ec-label" for="ec_contactname">
                        Contact Name<span class="required-asterisk">*</span>
                    </label>
                </div>
                <div class="ec-item">
                    <input type="text"
                        class="external-customer-form-input"
                        id="ec_contactname"
                        name="ec_contactname"
                        value="{{ isset($customer) ? $customer->EC_ContactName  : '' }}"
                        required aria-required="true"
                        @include('includes.input-text-name-regex')>
                </div>

                <div class="ec-item">
                    <label class="ec-label" for="ec_position">Position</label>
                </div>
                <div class="ec-item">
                    <input type="text"
                        class="external-customer-form-input"
                        id="ec_position"
                        name="ec_position"
                        value="{{ isset($customer) ? $customer->EC_Position  : '' }}"
                        @include('includes.input-text-name-regex')>
                </div>
            </div>

            {{-- Row 3 --}}
            <div class="ec-row">
                <div class="ec-item">
                    <label class="ec-label" for="ec_telephonenumber">
                        Telephone Number<span class="required-asterisk">*</span>
                    </label>
                </div>
                <div class="ec-item">
                    <input type="text"
                        class="external-customer-form-input"
                        id="ec_telephonenumber"
                        name="ec_telephonenumber"
                        value="{{ isset($customer) ? $customer->EC_ContactNumber  : '' }}"
                        required aria-required="true"
                        @include('includes.input-telephone-number-regex')>
                </div>

                <div class="ec-item">
                    <label class="ec-label" for="ec_email">
                        Email Address<span class="required-asterisk">*</span>
                    </label>
                </div>
                <div class="ec-item">
                    <input type="email"
                        class="external-customer-form-input"
                        id="ec_email"
                        name="ec_email"
                        value="{{ isset($customer) ? $customer->EC_ContactEmail  : '' }}"
                        required aria-required="true">
                </div>
            </div>

            {{-- Row 4 --}}
            <div class="ec-row ec-heading" aria-level="2">
                <h2 class="ec-item" style="flex: 0 0 100%;font-size: 12px; margin: 3px 0px;">Company Details</h2>
            </div>

            {{-- Row 5 --}}
            <div class="ec-row">
                <div class="ec-item">
                    <label class="ec-label" for="ec_companyname">
                        Company Name<span class="required-asterisk">*</span>
                    </label>
                </div>
                <div class="ec-item">
                    <input type="text"
                        class="external-customer-form-input"
                        id="ec_companyname"
                        name="ec_companyname"
                        value="{{ isset($customer) ? $customer->EC_CompanyName  : '' }}"
                        required aria-required="true"
                        @include('includes.input-text-name-regex')>
                </div>

                <div class="ec-item">
                    <span class="ec-label">
                        Company Address<span class="required-asterisk">*</span>
                    </span>
                </div>

                <div class="ec-item" id="company-address-container">
                    <a href="javascript:void(0);" id="company_address_open_frm" class="ec-update">Update</a>
                </div>

            </div>
        </div>

        @csrf
    </form>

    @if(isset($customer))
    <input type="hidden" id="external-customer-id" value="{{ $customer->EC_ExternalCustomerID }}">
    @endif

    <div style="text-align: right; background-color: white">
        <button
            type="button"
            style="font-size:12px; background: #d0d0d0; margin-top: 10px;"
            id="submit-customer-form-button"
            class="ui-button ui-widget ui-corner-all">
            @if(!isset($customer)) Create Customer @else Update Customer @endif
        </button>
    </div>

    @include('pages.customer.customerAddressLink')
</div>