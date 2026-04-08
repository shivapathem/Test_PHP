<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Carbon\Carbon;

interface FacilityBookingAdminRepositoryInterface
{
    /**
     * Gets the list of facility details and its bookings
     *
     * @param User $user
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getAdministratorFacilityBookings(User $user, Carbon $startDate, Carbon $endDate, string $status, string $list_type): array;
}
