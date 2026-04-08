<?php

namespace App\Repositories\Contracts;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBookerNote;
use App\Models\FacilityBooking\FacilityBookerNoteRecurrence;
use App\Models\FacilityBooking\FacilityBooking;
use App\Models\FacilityBooking\FacilityBookingRecurrence;
use App\Models\HistoryLog;
use App\Models\User;
use Carbon\Carbon;

interface FacilityBookingRepositoryInterface
{
    /**
     * Gets the list of facility details and its bookings
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param boolean $showCanceled
     * @param array $showOnlyStatus
     * @param array $facilityIds
     * @return array
     */
    public function getFacilityBookings(Carbon $startDate, Carbon $endDate, bool $showCanceled, array $showOnlyStatus, array $facilityIds = []): array;

    /**
     * Store Facility Booking Recurrence
     *
     * @param Facility $facility Facility
     * @param array $facilityBookingData Facility booking data
     * @param User $user user performing action
     * @param FacilityBookingRecurrence $editFacilityBookingRecurrence
     *
     */
    public function saveFacilityBookingRecurrence(Facility $facility, array $facilityBookingData, User $user, ?FacilityBookingRecurrence $editFacilityBookingRecurrence = null);

    /**
     * Update Facility Booking
     *
     * @param FacilityBooking $facilityBooking Facility booking
     * @param array $facilityBookingFormData Facility booking form data
     * @param User $user user performing action
     *
     */
    public function updateFacilityBooking(FacilityBooking $facilityBooking, array $facilityBookingFormData, User $user);

    /**
     * Get Cancel facility booking data
     *
     * @param FacilityBooking $facilityBooking Facility booking
     * @param array $facilityBookingFormData Facility booking form data
     * @param User $user user performing action
     *
     */
    public function cancelFacilityBooking(FacilityBooking $facilityBooking, array $facilityBookingFormData, User $user);

    /**
     * Cancel facility booking
     *
     * @param FacilityBooking $facilityBooking Facility booking
     * @param array $facilityBookingFormData Facility booking form data
     * @param User $user user performing action
     *
     */
    public function cancelFacilityBookingData(FacilityBooking $facilityBooking, array $facilityBookingFormData, User $user);

    /**
     * Store Facility Booker Note Recurrence
     *
     * @param Facility $facility Facility
     * @param array $facilityBookerNoteData Facility booker note data
     * @param User $user user performing action
     * @param FacilityBookerNoteRecurrence $editFacilityBookerNoteRecurrence edit recurrence
     *
     */
    public function saveFacilityBookerNoteRecurrence(Facility $facility, array $facilityBookerNoteData, User $user, ?FacilityBookerNoteRecurrence $editFacilityBookerNoteRecurrence = null);

    /**
     * Update facility booker note
     * @param FacilityBookerNote $facilityBookerNote
     * @param array $facilityBookerNoteData
     * @param User $user
     */
    public function updateFacilityBookerNote(FacilityBookerNote $facilityBookerNote, array $facilityBookerNoteData, User $user);

    /**
     * Delete scheduler notes
     *
     * @param FacilityBookerNote $facilityBookerNote Facility booker note
     * @param User $user user performing action
     *
     */
    public function deleteSchedulerNote(FacilityBookerNote $facilityBookerNote, User $user): void;

    /**
     * Store Facility Booking history
     *
     * @param FacilityBooking $facilityBooking
     * @param array $facilityHistoryLog
     * @param User $user
     */
    public function storeFacilityBookingHistory(FacilityBooking $facilityBooking, array $facilityBookingHistoryLog, User $user): ?HistoryLog;
}
