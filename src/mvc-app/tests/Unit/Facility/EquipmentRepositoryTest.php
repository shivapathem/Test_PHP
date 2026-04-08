<?php

namespace Tests\Unit\Facility;

use App\Models\Facility\Equipment;
use App\Models\User;
use App\Repositories\EquipmentRepository;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
class EquipmentRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getAllEquipment_returns()
    {
        // Mock the Equipment model's all() method
        $mockEquipment = Mockery::mock('alias:' . Equipment::class);
        $mockCollection = Mockery::mock(Collection::class);
        $mockEquipment->shouldReceive('all')->once()->andReturn($mockCollection);

        $repository = new EquipmentRepository();
        $result = $repository->getAllEquipment();

        $this->assertSame($mockCollection, $result);
    }

}
