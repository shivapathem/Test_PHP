<?php

namespace Tests\Unit\Facility;

use Tests\TestCase;
use App\Repositories\LocationRepository;
use App\Models\Facility\Location;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class LocationRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new LocationRepository();
    }

    #[Test]
    public function it_gets_all_locations()
    {
        // Arrange: mock Location model's static all() method
        $mockLocation = Mockery::mock('overload:' . Location::class);
        $mockCollection = Mockery::mock('Illuminate\Support\Collection');

        $mockLocation->shouldReceive('all')
            ->once()
            ->andReturn($mockCollection);

        // Act
        $result = $this->repository->getAllLocation();

        // Assert
        $this->assertSame($mockCollection, $result);
    }
}
