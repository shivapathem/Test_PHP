<?php

namespace App\Repositories;

use App\Models\Facility\Action;
use App\Repositories\Contracts\ActionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ActionRepository implements ActionRepositoryInterface
{
    /**
     * Get all actions
     *
     * @return Collection
     */
    public function getAllActions(): Collection
    {
        return Action::orderBy('action_name')->get();
    }

    /**
     * Get action by ID
     *
     * @param int $id
     * @return Action|null
     */
    public function getActionById(int $id): ?Action
    {
        return Action::find($id);
    }

    /**
     * Save new action
     *
     * @param array $data
     * @param $user
     * @return Action
     */
    public function saveAction(array $data, $user): Action
    {
        $action = new Action();
        $action->action_name = $data['action_name'];
        $action->description = $data['description'];
        $action->save();

        return $action;
    }

    /**
     * Update action
     *
     * @param Action $action
     * @param array $data
     * @param $user
     * @return Action
     */
    public function updateAction(Action $action, array $data, $user): Action
    {
        $action->action_name = $data['action_name'];
        $action->description = $data['description'];
        $action->save();

        return $action;
    }

    /**
     * Delete action
     *
     * @param Action $action
     * @param $user
     * @return bool
     */
    public function destroyAction(Action $action, $user): bool
    {
        return $action->delete();
    }

    /**
     * Get actions list for API
     *
     * @return array
     */
    public function getActionsList(): array
    {
        return Action::select('action_id as id', 'action_name as name', 'description')
            ->orderBy('action_name')
            ->get()
            ->toArray();
    }

    /**
     * Get count of future bookings linked to an action
     *
     * @param Action $action
     * @return int
     */
    public function getFutureBookingsCount(Action $action): int
    {
        return $action->facilityBookings()
            ->whereDate('FB_BookingStartDateTime', '>=', Carbon::now())
            ->count();
    }

    /**
     * Remove action from all future bookings
     *
     * @param Action $action
     * @return int Number of bookings affected
     */
    public function removeActionFromFutureBookings(Action $action): int
    {
        $futureBookingIds = $action->facilityBookings()
            ->whereDate('FB_BookingStartDateTime', '>=', Carbon::now())
            ->pluck('FacilityBookings.FB_FacilityBookingID')
            ->toArray();

        if (empty($futureBookingIds)) {
            return 0;
        }

        DB::table('FacilityBookingActions')
            ->where('FBA_ActionID', $action->action_id)
            ->whereIn('FBA_FacilityBookingID', $futureBookingIds)
            ->delete();

        return count($futureBookingIds);
    }
}
