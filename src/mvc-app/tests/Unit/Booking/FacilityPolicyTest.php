<?php

namespace Tests\Unit\Booking;

use App\Models\Facility\Facility;
use App\Models\User;
use App\Policies\FacilityPolicy;
use Tests\TestCase;

class FacilityPolicyTest extends TestCase
{
    protected $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new FacilityPolicy();
    }

    public function testViewAny()
    {
        $user = new User([ 'UD_NetLogin' => 'testuser', 'UD_DisplayName' => 'Test User' ]);
        $user->isFacilityAdministrator = 1;
        $user->isDivisionalAdmin = 0;

        $this->assertTrue($this->policy->viewAny($user));

        $user->isFacilityAdministrator = 0;
        $user->isDivisionalAdmin = 1;
        $this->assertTrue($this->policy->viewAny($user));

        $user->isDivisionalAdmin = 0;
        $this->assertFalse($this->policy->viewAny($user));
    }

    public function testCreate()
    {
        $user = new User([ 'UD_NetLogin' => 'testuser2', 'UD_DisplayName' => 'Test User 2' ]);
        $user->isRealFacilityAdministrator = 1;

        $this->assertTrue($this->policy->create($user));

        $user->isRealFacilityAdministrator = 0;
        $this->assertFalse($this->policy->create($user));
    }

    // Add more test methods for other policy methods
}
