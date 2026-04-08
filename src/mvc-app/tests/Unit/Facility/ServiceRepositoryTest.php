<?php

namespace Tests\Unit\Facility;

use App\Models\Facility\Service as FacilityService;
use App\Models\User;
use App\Repositories\ServiceRepository;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
class ServiceRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getAllService_returns_all_services()
    {
        // Mock the Service model's all() method
        $mockService = Mockery::mock('alias:' . FacilityService::class);
        $collection = Mockery::mock(Collection::class);
        $mockService->shouldReceive('all')->once()->andReturn($collection);

        $repository = new ServiceRepository($mockService);
        $result = $repository->getAllService();

        $this->assertSame($collection, $result);
    }

}
