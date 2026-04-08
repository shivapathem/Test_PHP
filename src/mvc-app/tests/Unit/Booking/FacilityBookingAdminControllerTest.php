<?php

namespace Tests\Unit\Booking;

use App\Http\Controllers\Booking\FacilityBookingAdminController;
use App\Models\User;
use App\Repositories\Contracts\FacilityBookingAdminRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Mockery;

class FacilityBookingAdminControllerTest extends TestCase
{
    protected $controller;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(FacilityBookingAdminRepositoryInterface::class);
        $this->controller = new FacilityBookingAdminController($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndex()
    {
        $response = $this->controller->index();
        $this->assertEquals('pages.admin.facility-booking-administrator.facility-booking-admin', $response->getName());
    }

    public function testGetTabData()
    {
        $user = Mockery::mock(User::class);
        Auth::shouldReceive('user')->andReturn($user);

        $request = new Request();
        $request->merge(['tabType' => 'new']);

        $this->mockRepository->shouldReceive('getAdministratorFacilityBookings')->twice()->andReturn([]);

        $response = $this->controller->getTabData($request);
        $this->assertEquals('pages.admin.facility-booking-administrator.facility-booking-admin-list', $response->getName());
    }
}
