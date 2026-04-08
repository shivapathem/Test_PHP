<?php

namespace Tests\Unit\Scheduling;

use Tests\TestCase;
use App\Repositories\SchedulingGroupRepository;

class SchedulingGroupRepositoryTest extends TestCase
{
    protected $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new SchedulingGroupRepository();
    }

    /**
     * Test that repository can be instantiated
     */
    public function testRepositoryCanBeInstantiated()
    {
        $this->assertInstanceOf(SchedulingGroupRepository::class, $this->repository);
    }

    /**
     * Test getAllSchedulingGroups method exists and is callable
     */
    public function testGetAllSchedulingGroupsMethodExists()
    {
        $this->assertTrue(method_exists($this->repository, 'getAllSchedulingGroups'));
        $this->assertIsCallable([$this->repository, 'getAllSchedulingGroups']);
    }

    /**
     * Test getSchedulingGroupsByDivision method exists and is callable
     */
    public function testGetSchedulingGroupsByDivisionMethodExists()
    {
        $this->assertTrue(method_exists($this->repository, 'getSchedulingGroupsByDivision'));
        $this->assertIsCallable([$this->repository, 'getSchedulingGroupsByDivision']);
    }

    /**
     * Test saveSchedulingGroup method exists and is callable
     */
    public function testSaveSchedulingGroupMethodExists()
    {
        $this->assertTrue(method_exists($this->repository, 'saveSchedulingGroup'));
        $this->assertIsCallable([$this->repository, 'saveSchedulingGroup']);
    }

    /**
     * Test updateSchedulingGroup method exists and is callable
     */
    public function testUpdateSchedulingGroupMethodExists()
    {
        $this->assertTrue(method_exists($this->repository, 'updateSchedulingGroup'));
        $this->assertIsCallable([$this->repository, 'updateSchedulingGroup']);
    }

    /**
     * Test destroySchedulingGroup method exists and is callable
     */
    public function testDestroySchedulingGroupMethodExists()
    {
        $this->assertTrue(method_exists($this->repository, 'destroySchedulingGroup'));
        $this->assertIsCallable([$this->repository, 'destroySchedulingGroup']);
    }

    /**
     * Test getAreaTeams method exists and is callable
     */
    public function testGetAreaTeamsMethodExists()
    {
        $this->assertTrue(method_exists($this->repository, 'getAreaTeams'));
        $this->assertIsCallable([$this->repository, 'getAreaTeams']);
    }
}
