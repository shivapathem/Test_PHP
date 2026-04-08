const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/menu.js', 'public/js/smartmenus')
    .js('resources/js/facility/facility-type.js', 'public/js/facility')
    .js('resources/js/facility/location.js', 'public/js/facility')
    .postCss('resources/css/app.css', 'public/css')
    .postCss('resources/css/custom.css', 'public/css')
    .postCss('resources/css/facility/facility.css', 'public/css/facility')
    .postCss('resources/css/facility/facilityType.css', 'public/css/facilityType')
    .postCss('resources/css/facility/facilityForm.css', 'public/css/facilityForm')
    .postCss('resources/css/facility/facilityAvailability.css', 'public/css/facilityAvailability')
    .postCss('resources/css/facility/facilityMarkAsUnavailable.css', 'public/css/facilityMarkAsUnavailable')
    .postCss('resources/css/facility/facilityTechnicalSetup.css', 'public/css/facilityTechnicalSetup')
    .postCss('resources/css/facility/locationExternalInternational.css', 'public/css/locationExternalInternational')
    .postCss('resources/css/facility/locationExternalUK.css', 'public/css/locationExternalUK')
    .postCss('resources/css/facility-booking/facility-booking-form.css', 'public/css/facility-booking')
    .js('resources/js/facility/facility.js', 'public/js/facility')
    .js('resources/js/facility/form/facilityMarkAsUnavailable.js', 'public/js/facility/form')
    .js('resources/js/facility/service.js', 'public/js/facility')
    .js('resources/js/facility/equipment.js', 'public/js/facility')
    .postCss('resources/css/font-awesome.css', 'public/css')
    .js('resources/js/facility/facility-filter.js', 'public/js/facility')
    .js('resources/js/booking/facility-booking.js', 'public/js/booking')
    .js('resources/js/booking/facility-booking-admin.js', 'public/js/booking')
    .postCss('resources/css/booking/facility-booking.css', 'public/css/booking')
    .js('resources/js/facility/action.js', 'public/js/facility')
    .js('resources/js/customer/customer.js', 'public/js/customer')
    .js('resources/js/booking/facility-booking-filter.js', 'public/js/booking')
    .js('resources/js/booking/facility-booking-websocket.js', 'public/js/booking')
    .js('resources/js/scheduling/scheduling.js', 'public/js/scheduling')
    .postCss('resources/css/email.css', 'public/css')
    .postCss('resources/css/customer/customerForm.css', 'public/css/customer')
    .postCss('resources/css/customer/customerAddressLink.css', 'public/css/customer')
    .js('resources/js/admin/allocate-users/allocate-users.js', 'public/js/admin/allocate-users')
    .postCss('resources/css/admin/admin.css', 'public/css/admin')
    .postCss('resources/css/admin/allocate-users.css', 'public/css/admin')
    .postCss('resources/css/admin/user-info.css', 'public/css/admin')
    .js('resources/js/scheduled-people/create-scheduled-person.js', 'public/js/scheduled-people')
    .postCss('resources/css/scheduled-people/create-scheduled-person.css', 'public/css/scheduled-people')
    .postCss('resources/css/scheduled-people/modals.css', 'public/css/scheduled-people')
    .postCss('resources/css/scheduled-people/contact-history.css', 'public/css/scheduled-people')
    .postCss('resources/css/scheduled-people/schedulepeople.css', 'public/css/scheduled-people')
    .js('resources/js/scheduled-people/staff-details.js', 'public/js/scheduled-people')
    .js('resources/js/scheduled-people/tab-list.js', 'public/js/scheduled-people')

    .js('resources/js/scheduled-people/tab-contract-history.js', 'public/js/scheduled-people')

    .js('resources/js/scheduled-people/team-history.js', 'public/js/scheduled-people')

    .version();
