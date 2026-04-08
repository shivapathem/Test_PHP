<?php

namespace App\Repositories\Contracts;

use App\Models\Facility\Action;
use Illuminate\Support\Collection;

interface ActionRepositoryInterface
{
    /**
     * Get all actions
     *
     * @return Collection
     */
    public function getAllActions(): Collection;

    /**
     * Get action by ID
     *
     * @param int $id
     * @return Action|null
     */
    public function getActionById(int $id): ?Action;

    /**
     * Save new action
     *
     * @param array $data
     * @param $user
     * @return Action
     */
    public function saveAction(array $data, $user): Action;

    /**
     * Update action
     *
     * @param Action $action
     * @param array $data
     * @param $user
     * @return Action
     */
    public function updateAction(Action $action, array $data, $user): Action;

    /**
     * Delete action
     *
     * @param Action $action
     * @param $user
     * @return bool
     */
    public function destroyAction(Action $action, $user): bool;

    /**
     * Get actions list for API
     *
     * @return array
     */
    public function getActionsList(): array;

    /**
     * Get count of future bookings linked to an action
     *
     * @param Action $action
     * @return int
     */
    public function getFutureBookingsCount(Action $action): int;

    /**
     * Remove action from all future bookings
     *
     * @param Action $action
     * @return int Number of bookings affected
     */
    public function removeActionFromFutureBookings(Action $action): int;
}
