<?php

namespace Tests\Unit\Booking;

use App\Http\Controllers\Booking\FacilityBookingViewController;
use App\Models\Facility\Action;
use App\Models\Facility\Facility;
use App\Models\FacilityBooking\ExternalCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
class FacilityBookingViewControllerTest extends TestCase
{
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new FacilityBookingViewController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndex()
    {
        $response = $this->controller->index();
        $this->assertEquals('pages.facility-booking.facility-booking', $response->getName());
    }

    public function testCreate()
    {
        $mockAction = Mockery::mock('alias:' . Action::class);
        $mockAction->shouldReceive('all')->once()->andReturn(collect([]));

        $mockExternalCustomer = Mockery::mock('alias:' . ExternalCustomer::class);
        $mockExternalCustomer->shouldReceive('getListOfExternalCustomers')->once()->andReturn(collect([]));

        $facility = new Facility();
        $facility->FC_FacilityName = 'Test Facility';
        $facility->FC_DefaultBookingType = 'self_booked';
        $facility->setRelation('linkedFacilities', collect([]));

        $user = Mockery::mock(User::class);
        $user->shouldReceive('haveAccessToFacility')->with($facility)->andReturn(false);
        Auth::shouldReceive('user')->andReturn($user);

        $request = new Request();
        $request->merge(['date_selected' => '2023-01-01', 'click_position_datetime' => '2023-01-01 10:00:00']);

        $response = $this->controller->create($facility, $request);
        $this->assertEquals('pages.facility-booking.form.facility-booking-form', $response->getName());
    }
}
