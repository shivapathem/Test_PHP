<?php

namespace Tests\Unit\Booking;

use App\Http\Controllers\Booking\FacilityBookingApiController;
use App\Http\Requests\StoreFacilityBookingRequest;
use App\Models\Facility\Facility;
use App\Models\User;
use App\Repositories\Contracts\FacilityBookingRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Mockery;

class FacilityBookingApiControllerTest extends TestCase
{
    protected $mockRepository;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(FacilityBookingRepositoryInterface::class);
        $this->controller = new FacilityBookingApiController($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testStore()
    {
        $facility = new Facility();
        $facility->FC_FacilityID = 1;
        $facility->setRelation('linkedFacilities', collect([]));

        $user = Mockery::mock(User::class);
        Auth::shouldReceive('user')->andReturn($user);

        $requestData = [
            'facilityBookingListSetting' => [
                'start_date' => '2023-01-01',
                'end_date' => '2023-01-07',
            ],
            // Add other data
        ];

        $request = new StoreFacilityBookingRequest();
        $request->merge($requestData);

        $this->mockRepository->shouldReceive('saveFacilityBookingRecurrence')->once();
        $this->mockRepository->shouldReceive('getFacilityBookings')->once()->andReturn([]);

        $response = $this->controller->store($facility, $request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    // Add more test methods for other controller methods
}
