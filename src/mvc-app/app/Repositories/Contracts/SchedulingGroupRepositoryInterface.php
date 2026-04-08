<?php

namespace App\Repositories\Contracts;

interface SchedulingGroupRepositoryInterface
{
    /**
     * Get all scheduling groups
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSchedulingGroups();

    /**
     * Get scheduling groups for a specific area (division)
     *
     * @param int $divisionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSchedulingGroupsByDivision($divisionId);

    /**
     * Save a new scheduling group
     *
     * @param array $data
     * @param \App\Models\User $user
     * @return \App\Models\Scheduling\SchedulingGroup
     */
    public function saveSchedulingGroup($data, $user);

    /**
     * Update an existing scheduling group
     *
     * @param \App\Models\Scheduling\SchedulingGroup $schedulingGroup
     * @param array $data
     * @param \App\Models\User $user
     * @return \App\Models\Scheduling\SchedulingGroup
     */
    public function updateSchedulingGroup($schedulingGroup, $data, $user);

    /**
     * Delete a scheduling group
     *
     * @param \App\Models\Scheduling\SchedulingGroup $schedulingGroup
     * @param \App\Models\User $user
     * @return bool
     */
    public function destroySchedulingGroup($schedulingGroup, $user);

    /**
     * Get scheduling teams for a specific area (division)
     *
     * @param \App\Models\Scheduling\Division $area
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAreaTeams($area);
}
