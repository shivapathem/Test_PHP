<?php

namespace Tests\Unit\Facility;

use Tests\TestCase;
use App\Repositories\FacilityTypeRepository;
use App\Models\Facility\FacilityType;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class FacilityTypeRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new FacilityTypeRepository();
    }

    #[Test]
    public function it_returns_all_facility_types_with_subtypes()
    {
        // Arrange: mock FacilityType model and its with()->get() chain
        $mockFacilityType = Mockery::mock('overload:' . FacilityType::class);
        $mockCollection = Mockery::mock('Illuminate\Support\Collection');

        $mockFacilityType->shouldReceive('with')
            ->once()
            ->with('facilitySubType')
            ->andReturnSelf();

        $mockFacilityType->shouldReceive('get')
            ->once()
            ->andReturn($mockCollection);

        // Act
        $result = $this->repository->getAllFacilityType();

        // Assert
        $this->assertSame($mockCollection, $result);
    }
}
