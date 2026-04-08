<?php

namespace Tests\Unit\Booking;

use App\Models\Facility\Facility;
use App\Models\User;
use App\Repositories\FacilityBookingAdminRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class FacilityBookingAdminRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FacilityBookingAdminRepository();
    }

    public function testGetAdministratorFacilityBookings()
    {
        $facility = new Facility();
        $facility->FC_DefaultBookingType = 'self_booked';
        $facility->current_location = null;

        $facilityBookingRecurrence = new class {
            public $is_recurring = false;
        };

        $facilityBookingData = new class($facility, $facilityBookingRecurrence) {
            public $facility;
            public $facilityBookingRecurrence;
            public $linkedToFacilityBooking = null;
            public $linkedFacilityBookings;
            public $externalCustomer = null;
            public $FB_FacilitySubTypeID = null;
            public $FB_BookingTitle = 'Test Booking';

            public function __construct($facility, $facilityBookingRecurrence)
            {
                $this->facility = $facility;
                $this->facilityBookingRecurrence = $facilityBookingRecurrence;
                $this->linkedFacilityBookings = collect([]);
            }

            public function toArray()
            {
                return ['FB_BookingTitle' => 'Test Booking'];
            }
        };

        $user = Mockery::mock(User::class);
        $user->shouldReceive('haveAccessToFacility')->with($facility)->andReturn(true);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->once()->with('FB_BookingStartDateTime', '>', Mockery::type(Carbon::class))->andReturnSelf();
        $queryMock->shouldReceive('where')->once()->with('FB_BookingStatus', '=', 'new')->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn(collect([$facilityBookingData]));

        $repository = new class($queryMock) extends FacilityBookingAdminRepository {
            private $queryMock;

            public function __construct($queryMock)
            {
                $this->queryMock = $queryMock;
            }

            protected function facilityBookingQuery()
            {
                return $this->queryMock;
            }
        };

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(3);

        $result = $repository->getAdministratorFacilityBookings($user, $startDate, $endDate, 'new', 'New_Future');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame('Test Booking', $result[0]['facility_bookings']['FB_BookingTitle']);
    }
}
