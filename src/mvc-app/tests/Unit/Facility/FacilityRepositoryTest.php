<?php

namespace Tests\Unit\Facility;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBooking;
use App\Models\Scheduling\Division;
use App\Models\User;
use App\Repositories\FacilityRepository;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class FacilityRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getFacilityList_returns_collection()
    {
        // Mock the Facility model's with() method and chained get() method
        $mockFacility = Mockery::mock('overload:' . Facility::class);
        $mockCollection = Mockery::mock(Collection::class);

        $mockQuery = Mockery::mock();
        $mockFacility->shouldReceive('with')->once()->with([
            'facilityAreaOwner:DivisionID,DivisionName',
            'location',
            'internalLocation:LN_LocationID,LN_Location',
            'facilityType:FT_FacilityTypeID,FT_FacilityType',
            'facilitySubTypes:FST_FacilitySubTypeID,FST_FacilitySubType',
            'facilityServices:SR_ServiceID,SR_Service',
            'facilityEquipments:EQ_EquipmentID,EQ_Equipment',
            'facilityAvailability',
            'facilityMarkAsUnavailable',
            'facilityRestrictBookers',
            'facilityAreaOwner.schedulingTeam',
            'linkedFacilities'
        ])->andReturn($mockQuery);
        $mockQuery->shouldReceive('get')->once()->andReturn($mockCollection);

        $repository = new FacilityRepository();
        $result = $repository->getFacilityList();

        $this->assertSame($mockCollection, $result);
    }

    public function test_getAreaList_returns_active_divisions()
    {
        $divisions = collect([(object) ['DivisionID' => 1, 'DivisionName' => 'Test Area']]);

        $mockQuery = Mockery::mock();
        $mockDivision = Mockery::mock('overload:' . Division::class);
        $mockDivision->shouldReceive('active')->once()->andReturn($mockQuery);
        $mockQuery->shouldReceive('get')->once()->andReturn($divisions);

        $repository = new FacilityRepository();
        $result = $repository->getAreaList();

        $this->assertSame($divisions, $result);
    }

    public function test_getAreaList_filters_by_facility_administrator_role_when_user_is_provided()
    {
        $divisions = collect([
            (object) ['DivisionID' => 1, 'DivisionName' => 'Area One'],
            (object) ['DivisionID' => 2, 'DivisionName' => 'Area Two'],
        ]);

        $user = new User();
        $user->getAreasRoles = collect([
            (object) ['RoleName' => 'Facility Administrator', 'DivisionID' => 2],
        ]);

        $mockQuery = Mockery::mock();
        $mockDivision = Mockery::mock('overload:' . Division::class);
        $mockDivision->shouldReceive('active')->once()->andReturn($mockQuery);
        $mockQuery->shouldReceive('get')->once()->andReturn($divisions);

        $repository = new FacilityRepository();
        $result = $repository->getAreaList($user);

        $this->assertCount(1, $result);
        $this->assertEquals(2, $result->first()->DivisionID);
    }

    public function test_getAreaTeamList_returns_scheduling_team()
    {
        $team = collect([(object) ['TeamID' => 123]]);
        $area = new Division();
        $area->schedulingTeam = $team;

        $repository = new FacilityRepository();
        $result = $repository->getAreaTeamList($area);

        $this->assertSame($team, $result);
    }

    public function test_facilityAreaList_returns_array()
    {
        $divisions = collect([(object) ['DivisionID' => 1, 'DivisionName' => 'Area A']]);

        $mockQuery = Mockery::mock();
        $mockDivision = Mockery::mock('overload:' . Division::class);
        $mockDivision->shouldReceive('select')->once()->with('DivisionID', 'DivisionName')->andReturn($mockQuery);
        $mockQuery->shouldReceive('where')->once()->with('isActive', 1)->andReturn($mockQuery);
        $mockQuery->shouldReceive('get')->once()->andReturn($divisions);

        $repository = new FacilityRepository();
        $result = $repository->facilityAreaList();

        $this->assertSame($divisions->toArray(), $result);
    }

    public function test_normalizeEmails_trims_lowercases_and_removes_duplicates()
    {
        $repository = new FacilityRepository();

        $result = $repository->normalizeEmails(' A@example.com, b@EXAMPLE.com , a@example.com , ');

        $this->assertSame(['a@example.com', 'b@example.com'], $result);
    }

    public function test_countFutureBookingsByStatus_returns_booking_count()
    {
        $query = Mockery::mock();
        $facility = new class($query) {
            private $query;

            public function __construct($query)
            {
                $this->query = $query;
            }

            public function facilityBookings()
            {
                return $this->query;
            }
        };

        $query->shouldReceive('whereIn')->once()->with('FB_BookingStatus', [FacilityBooking::BOOKING_STATUS_NEW, FacilityBooking::BOOKING_STATUS_PENDING])->andReturnSelf();
        $query->shouldReceive('whereBetween')->once()->with('FB_BookingStartDateTime', Mockery::type('array'))->andReturnSelf();
        $query->shouldReceive('count')->once()->andReturn(3);

        $repository = new FacilityRepository();
        $result = $repository->countFutureBookingsByStatus($facility, 1, 3);

        $this->assertSame(3, $result);
    }

}
