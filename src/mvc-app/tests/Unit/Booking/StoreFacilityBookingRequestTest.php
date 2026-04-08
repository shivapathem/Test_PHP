<?php

namespace Tests\Unit\Booking;

use App\Http\Requests\StoreFacilityBookingRequest;
use App\Models\Facility\Facility;
use App\Models\Scheduling\Division;
use App\Models\Facility\FacilityType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Routing\Route;
use Tests\TestCase;

class StoreFacilityBookingRequestTest extends TestCase
{
    public function testAuthorize()
    {
        $request = new StoreFacilityBookingRequest();
        $this->assertTrue($request->authorize());
    }

    public function testRules()
    {
        $facility = new Facility();
        $facility->FC_ActiveFrom = Carbon::now()->subDay();
        $facility->FC_DefaultBookingType = 'self_booked';
        $facility->FC_MakeAllBookingsPrivate = 'no';
        $facility->FC_AllowBookingRequest = true;
        $facility->FC_OneOffBookingsOnly = false;
        $facility->FC_AllowSelfBookingFrom = 1;
        $facility->FC_AllowSelfBookingTo = 10;

        $request = new StoreFacilityBookingRequest();
        $route = $this->createMock(Route::class);
        $route->method('parameter')->willReturnCallback(function ($key) use ($facility) {
            return $key === 'facility' ? $facility : null;
        });
        $request->setRouteResolver(fn () => $route);

        // Set minimal required input data to avoid Carbon parsing errors
        $request->merge([
            'facilityBookingMainData' => [
                'booking_date' => now()->format('d/m/Y'),
                'booking_start_time' => '10:00',
                'booking_end_time' => '11:00',
                'facility_booking_data_type' => 'test',
                'facility_booking_facility_sub_type_form' => 'test',
                'facility_request_booking_title' => 'Test Booking',
                'private_booking' => 'no',
                'recurring_booking_enable' => 'no',
                'customer_type' => 'internal',
                'requestor_name' => 'Test User',
                'requestor_detail' => 'test@example.com',
            ]
        ]);

        $rules = $request->rules();
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('facilityBookingMainData', $rules);
    }
}
