<?php

namespace Tests\Unit\Booking;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBooking;
use App\Repositories\FacilityBookingRepository;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class FacilityBookingRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FacilityBookingRepository();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetFacilityBookings_returns_facility_data_array()
    {
        $facilityData = new class {
            public $FC_FacilityID = 1;
            public $FC_FacilityName = 'Test Facility';
            public $FC_ActiveFrom;
            public $FC_ProviderName = 'Provider';
            public $FC_AreaOwnerID = 10;
            public $FC_DefaultBookingType = 'self_booked';
            public $current_location = 'Test Location';
            public $FC_Accessible = true;
            public $FC_ArchivedDate = null;
            public $FC_AllowBookingRequest = true;
            public $FC_FacilityNote = 'Note';
            public $facilityType;
            public $facilitySubTypes;
            public $facilityServices;
            public $facilityEquipments;
            public $facilityBookings;
            public $facilityRestrictBookers;
            public $facilityBookerNotes;

            public function __construct()
            {
                $this->FC_ActiveFrom = Carbon::now()->subDay();
                $this->facilityType = (object) ['FT_FacilityType' => 'Type'];
                $this->facilitySubTypes = collect([(object) ['FST_FacilitySubType' => 'SubType']]);
                $this->facilityServices = collect([(object) ['SR_Service' => 'Service']]);
                $this->facilityEquipments = collect([(object) ['EQ_Equipment' => 'Equipment']]);
                $this->facilityBookings = collect([(object) ['FB_BookingTitle' => 'Booking']]);
                $this->facilityRestrictBookers = collect([(object) ['schedulingTeamId' => 123]]);
                $this->facilityBookerNotes = collect([(object) []]);
            }

            public function getUnavailableDateTime($startDate, $endDate)
            {
                return [];
            }
        };

        $facilityModel = Mockery::mock('alias:' . Facility::class);
        $queryMock = Mockery::mock();
        $facilityModel->shouldReceive('with')->once()->with(Mockery::on(function ($value) {
            return is_array($value)
                && in_array('location', $value, true)
                && in_array('facilityMarkAsUnavailable', $value, true)
                && in_array('facilityAvailability', $value, true)
                && in_array('facilityType:FT_FacilityTypeID,FT_FacilityType', $value, true)
                && isset($value['facilityBookings'])
                && isset($value['facilityBookerNotes']);
        }))->andReturn($queryMock);
        $queryMock->shouldReceive('orderBy')->once()->with('FC_FacilityName', FacilityBooking::BOOKING_SORT_ORDER_ASC)->andReturnSelf();
        $queryMock->shouldReceive('whereIn')->once()->with('FC_FacilityID', [1])->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn(collect([$facilityData]));

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(7);

        $result = $this->repository->getFacilityBookings($startDate, $endDate, false, [], [1]);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame('Test Facility', $result[0]['facility_name']);
    }
}
