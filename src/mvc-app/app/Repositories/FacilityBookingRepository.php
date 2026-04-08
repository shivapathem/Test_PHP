<?php

namespace App\Repositories;

use App\Mail\FacilityBooking\FacilityBookingStatusUpdateMail;
use App\Mail\FacilityBooking\FacilityBookingUpdateMail;
use App\Mail\FacilityBookingCancelled;
use App\Mail\FacilityBookingMail;
use App\Mail\FacilityBookingReinstateMail;
use App\Models\Facility\Action;
use App\Models\Facility\Facility;
use App\Models\Facility\FacilitySubType;
use App\Models\FacilityBooking\ExternalCustomer;
use App\Models\FacilityBooking\FacilityBookerNote;
use App\Models\FacilityBooking\FacilityBookerNoteRecurrence;
use App\Models\FacilityBooking\FacilityBooking;
use App\Models\FacilityBooking\FacilityBookingRecurrence;
use App\Models\User;
use App\Repositories\Contracts\FacilityBookingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\HistoryLog;
use App\Trait\FacilityUnAvailabilityBookingFormDataTrait;
use Illuminate\Support\Collection;

class FacilityBookingRepository implements FacilityBookingRepositoryInterface
{
    use FacilityUnAvailabilityBookingFormDataTrait;

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
    public function getFacilityBookings(Carbon $startDate, Carbon $endDate, bool $showCanceled, array $showOnlyStatus, array $facilityIds = []): array
    {
        $returnData = [];
        $queryData = Facility::with([
            'location',
            'facilityMarkAsUnavailable',
            'facilityAvailability',
            'internalLocation:LN_LocationID,LN_Location',
            'facilityType:FT_FacilityTypeID,FT_FacilityType',
            'facilitySubTypes:FST_FacilitySubTypeID,FST_FacilitySubType',
            'facilityServices:SR_ServiceID,SR_Service',
            'facilityEquipments:EQ_EquipmentID,EQ_Equipment',
            'facilityBookings' => function ($query) use ($startDate, $endDate, $showCanceled, $showOnlyStatus) {
                $query->where(function ($query2) use ($startDate, $endDate) {
                    $query2->whereBetween('FB_BookingStartDateTime', [$startDate, $endDate->endOfDay()])
                        ->orWhereBetween('FB_BookingEndDateTime', [$startDate, $endDate->endOfDay()]);
                })->select(
                    'FB_FacilityBookingID',
                    'FB_FacilityBookingRecurrenceID',
                    'FB_FacilityID',
                    'FB_BookingStartDateTime',
                    'FB_BookingEndDateTime',
                    'FB_BookingTitle',
                    'FB_BookingStatus',
                    'FB_CreatedBy',
                    'FB_Private'
                );
                if (!empty($showOnlyStatus)) {
                    if ($showCanceled) {
                        $showOnlyStatus[] = FacilityBooking::BOOKING_STATUS_CANCELLED;
                    }
                    $query->whereIn('FB_BookingStatus', $showOnlyStatus);
                } else {
                    if (!$showCanceled) {
                        $query->where('FB_BookingStatus', '!=', FacilityBooking::BOOKING_STATUS_CANCELLED);
                    }
                }
                $query->orderBy('FB_BookingStartDateTime', FacilityBooking::BOOKING_SORT_ORDER_ASC)
                    ->orderBy('FB_BookingEndDateTime', FacilityBooking::BOOKING_SORT_ORDER_ASC)
                    ->orderByRaw('LOWER(FB_BookingTitle)' . FacilityBooking::BOOKING_SORT_ORDER_ASC)
                    ->orderBy('FB_FacilityBookingID', FacilityBooking::BOOKING_SORT_ORDER_ASC);
            },
            'facilityBookerNotes' => function ($query) use ($startDate, $endDate) {
                $query->where(function ($query2) use ($startDate, $endDate) {
                    $query2->whereBetween('FBN_StartDateTime', [$startDate, $endDate->endOfDay()])
                        ->orWhereBetween('FBN_EndDateTime', [$startDate, $endDate->endOfDay()]);
                });
            },
            'facilityBookings.actions',
            'facilityBookings.linkedToFacilityBooking:FB_FacilityBookingID,FB_FacilityID',
            'facilityRestrictBookers'  => function ($query) {
                $query->select(
                    'schedulingTeamId',
                    'schedulingTeamName'
                );
            }
        ])->orderBy('FC_FacilityName', FacilityBooking::BOOKING_SORT_ORDER_ASC);

        if (!empty($facilityIds)) {
            $queryData->whereIn('FC_FacilityID', $facilityIds);
        } else {
            $queryData->where(function ($query) use ($startDate) {
                $query->whereNull('FC_ArchivedDate')
                    ->orWhere('FC_ArchivedDate', '>', $startDate->format('Y-m-d'));
            });
        }
        foreach ($queryData->get() as $key => $facilityData) {
            $returnData[$key]['facility_id'] = $facilityData->FC_FacilityID;
            $returnData[$key]['facility_name'] = $facilityData->FC_FacilityName;
            $returnData[$key]['facility_active_form'] = $facilityData->FC_ActiveFrom->format('Y-m-d');
            $returnData[$key]['facility_provider_name'] = $facilityData->FC_ProviderName;
            $returnData[$key]['facility_type'] = $facilityData->facilityType->FT_FacilityType;
            $returnData[$key]['facility_sub_types'] = $facilityData->facilitySubTypes->pluck('FST_FacilitySubType');
            $returnData[$key]['facility_area_owner_id'] = $facilityData->FC_AreaOwnerID;
            $returnData[$key]['facility_default_booking_type'] = $facilityData->FC_DefaultBookingType;
            $returnData[$key]['facility_current_location'] = $facilityData->current_location;
            $returnData[$key]['facility_services'] = $facilityData->facilityServices->pluck('SR_Service');
            $returnData[$key]['facility_equipments'] = $facilityData->facilityEquipments->pluck('EQ_Equipment');
            $returnData[$key]['facility_bookings'] = $facilityData->facilityBookings->toArray();
            $returnData[$key]['facility_restricted_bookers_team'] = $facilityData->facilityRestrictBookers->pluck('schedulingTeamId')->toArray();
            $returnData[$key]['facility_booker_notes'] = $facilityData->facilityBookerNotes->toArray();
            $returnData[$key]['facility_unavailable'] = $facilityData->getUnavailableDateTime($startDate, $endDate);
            $returnData[$key]['facility_accessible'] = $facilityData->FC_Accessible;
            $returnData[$key]['facility_archived'] = $facilityData->FC_ArchivedDate != null ? $facilityData->FC_ArchivedDate->format('Y-m-d') : '';
            $returnData[$key]['facility_allow_booking_request'] = $facilityData->FC_AllowBookingRequest;
            $returnData[$key]['facility_note'] = $facilityData->FC_FacilityNote;
        }
        return $returnData;
    }

    /**
     * Store Facility Booking Recurrence
     *
     * @param Facility $facility Facility
     * @param array $facilityBookingData Facility booking data
     * @param User $user user performing action
     * @param FacilityBookingRecurrence $editFacilityBookingRecurrence
     *
     */
    public function saveFacilityBookingRecurrence(Facility $facility, array $facilityBookingData, User $user, ?FacilityBookingRecurrence $editFacilityBookingRecurrence = null)
    {
        $alreadyTransactionEnabled = DB::transactionLevel() > 0;
        if (!$alreadyTransactionEnabled) {
            DB::beginTransaction();
        }
        try {
            $facilityBookingMainData = $facilityBookingData['facilityBookingMainData'];
            $facilityBookingRecurrenceData = $facilityBookingData['facilityBookingRecurring'];

            $facilityBookingRecurrence = $editFacilityBookingRecurrence ?? new FacilityBookingRecurrence();


            if ($facilityBookingMainData['moved_booking'] != 1) {
                //Facility ID
                $facilityBookingRecurrence->FBR_FacilityID = $facility->FC_FacilityID;
                //Facility sub type
                $facilityBookingRecurrence->FBR_FacilitySubTypeID = $facilityBookingMainData['facility_booking_facility_sub_type_form'];
            }

            //Booking type
            $facilityBookingRecurrence->FBR_BookingType = $facilityBookingMainData['facility_booking_data_type'];

            //Series Start date
            $bookingDate = Carbon::createFromFormat('d/m/Y', $facilityBookingMainData['booking_date']);
            $startDate = $facilityBookingMainData['recurring_booking_enable'] == 'yes' ? Carbon::createFromFormat('d/m/Y', $facilityBookingRecurrenceData['recurring_start_date']) : $bookingDate;
            $facilityBookingRecurrence->FBR_SeriesStartDate = $startDate->format('Y-m-d');

            //Series End date
            $endDate = $facilityBookingMainData['recurring_booking_enable'] == 'yes' ? Carbon::createFromFormat('d/m/Y', $facilityBookingRecurrenceData['recurring_end_date']) : $bookingDate;
            $facilityBookingRecurrence->FBR_SeriesEndDate = $endDate->format('Y-m-d');

            //Start Time
            $facilityBookingRecurrence->FBR_StartTime = $facilityBookingMainData['booking_start_time'];

            //End Time
            $facilityBookingRecurrence->FBR_EndTime = $facilityBookingMainData['booking_end_time'];

            //Appointment details
            if (isset($facilityBookingMainData['recurring_booking_enable'])) {
                if ($facilityBookingMainData['recurring_booking_enable'] == 'yes') {
                    if (isset($facilityBookingRecurrenceData['recurring_booking_recurrence_type'])) { // While move we dont send this details
                        $facilityBookingRecurrence->FBR_RecurrenceType = $facilityBookingRecurrenceData['recurring_booking_recurrence_type'];
                        if ($facilityBookingRecurrence->FBR_RecurrenceType == FacilityBookingRecurrence::RECUR_TYPE_DAILY) {
                            $facilityBookingRecurrence->FBR_RecurrenceDayInterval = $facilityBookingRecurrenceData['recurring_booking_recurrence_daily_days'];
                        } elseif ($facilityBookingRecurrence->FBR_RecurrenceType == FacilityBookingRecurrence::RECUR_TYPE_WEEKLY) {
                            $facilityBookingRecurrence->FBR_RecurrenceWeekInterval = $facilityBookingRecurrenceData['recurring_booking_recurrence_weekly_weeks'];
                            $days = ['Sunday', 'Saturday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                            foreach ($days as $dayLoop) { //Reset
                                $facilityBookingRecurrence->{'FBR_RecurrenceWeek_' . $dayLoop} = 0;
                            }
                            foreach ($facilityBookingRecurrenceData['recurring_booking_weekly_days'] as $day) {
                                $facilityBookingRecurrence->{'FBR_RecurrenceWeek_' . ucfirst($day)} = 1;
                            }
                        }
                    }
                } else {
                    $facilityBookingRecurrence->FBR_RecurrenceType = FacilityBookingRecurrence::RECUR_TYPE_DAILY;
                    $facilityBookingRecurrence->FBR_RecurrenceDayInterval = 1;
                }
            }

            //Booking Title
            $facilityBookingRecurrence->FBR_BookingTitle = $facilityBookingMainData['facility_request_booking_title'];


            //Private
            $facilityBookingRecurrence->FBR_Private = $facilityBookingMainData['private_booking'];

            //Customer type
            $facilityBookingRecurrence->FBR_CustomerType = $facilityBookingMainData['customer_type'];

            //External Customer ID
            if ($facilityBookingMainData['customer_type'] == FacilityBookingRecurrence::CUSTOMER_TYPE_EXTERNAL) {
                $facilityBookingRecurrence->FBR_ExternalCustomerID = $facilityBookingMainData['external_customer_company'];
            }

            //Customer details
            $facilityBookingRecurrence->FBR_ContactName = $facilityBookingMainData['customer_contact_name'] ?? null;
            $facilityBookingRecurrence->FBR_ContactTelephone = $facilityBookingMainData['customer_contact_telephone'] ?? null;
            $facilityBookingRecurrence->FBR_ContactEmail = $facilityBookingMainData['customer_contact_email'] ?? null;
            $facilityBookingRecurrence->FBR_RequestorName = $facilityBookingMainData['requestor_name'];
            $facilityBookingRecurrence->FBR_RequestorDetail = $facilityBookingMainData['requestor_detail'];
            $facilityBookingRecurrence->FBR_RequestorNote = $facilityBookingMainData['requestor_notes'];
            $facilityBookingRecurrence->FBR_SchedulerNote = $facilityBookingMainData['scheduler_notes'];

            $facilityBookingRecurrence->FBR_CreatedBy = $user->UD_UserID;
            $facilityBookingRecurrence->FBR_UpdatedBy = $user->UD_UserID;
            $availableDatesRecurrence = $facilityBookingRecurrence->getAvailableDates($user);

            //Update the first last recurring instance dates
            $facilityBookingRecurrence->FBR_SeriesStartDate = min($availableDatesRecurrence)->format('Y-m-d');
            $facilityBookingRecurrence->FBR_SeriesEndDate = max($availableDatesRecurrence)->format('Y-m-d');
            $facilityBookingRecurrence->save();

            //Facility links
            if ($facilityBookingMainData['moved_booking'] != 1) {
                $facilityBookingRecurrence->linkedFacilities()->sync($facilityBookingData['facilityBookingFacilityLink'] ?? []);
            }
            //Actions
            $actions = [];
            foreach ($facilityBookingData['facilityBookingActions'] ?? [] as $id => $actionData) {
                $actions[$id] = [
                    'FBRA_ActionStartTime' => $actionData['actionStartTime'],
                    'FBRA_ActionEndTime' => $actionData['actionEndTime']
                ];
            }
            $facilityBookingRecurrence->actions()->sync($actions);
            //Create bookings
            if ($editFacilityBookingRecurrence == null) {
                $linkedFacilityUnavailability = $this->linkedFacilityUnvailableDates($facility, $facilityBookingData, $user);
                $linkedFacilities = $facilityBookingRecurrence->linkedFacilities()->with('facilitySubTypes')->get();
                foreach ($availableDatesRecurrence as $availableDate) {
                    $newBooking = $this->saveFacilityBooking($facility, $facilityBookingRecurrence, $availableDate, $facilityBookingData, $user);

                    //Linked facilities
                    $linkedBookingIds = [];
                    foreach ($linkedFacilities as $linkedFacility) {
                        //Check the date is available
                        if (isset($linkedFacilityUnavailability[$linkedFacility->FC_FacilityID]) && in_array($availableDate->format('Y-m-d'), $linkedFacilityUnavailability[$linkedFacility->FC_FacilityID])) {
                            continue;
                        }
                        //Facility sub type will be different
                        $faciltySubType = $linkedFacility->facilitySubTypes->filter(function ($item) {
                            return $item->getOriginal('pivot_FCST_PrimarySubType') == 1;
                        })->first();
                        $facilityBookingDataLinked = $facilityBookingData;
                        $facilityBookingDataLinked['facilityBookingMainData']['facility_booking_facility_sub_type_form'] = $faciltySubType->FST_FacilitySubTypeID;
                        $linkedNewBooking = $this->saveFacilityBooking($linkedFacility, $facilityBookingRecurrence, $availableDate, $facilityBookingDataLinked, $user);
                        $linkedBookingIds[] = $linkedNewBooking->FB_FacilityBookingID;
                    }
                    if (!empty($linkedBookingIds)) {
                        $newBooking->linkedFacilityBookings()->attach($linkedBookingIds);
                    }
                }

                //Send Email
                Mail::to($user->UD_InternalEmail)
                    ->cc($facility->facility_contact_emails)
                    ->send(new FacilityBookingMail($user, $facilityBookingRecurrence));
            }
            if (!$alreadyTransactionEnabled) {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Linked facility Unavailable dates
     *
     * @param Facility $facility model
     * @param array $formData booking form data
     * @param FacilityBooking $facilityBooking facility booking
     */
    function linkedFacilityUnvailableDates(Facility $facility, array $formData, User $user, ?FacilityBooking $facilityBooking = null): array
    {
        $unavailabilityDetails = [];
        $facilityAccess = $user->haveAccessToFacility($facility);
        foreach ($this->getFacilityBookingUnavailabilityDate($facility, $formData, $facilityBooking) as $unavailability) {
            foreach ($unavailability['unavailability'] as $unavailabilityDate) {
                if ($facilityAccess && $unavailabilityDate['archived'] == 0 && $unavailabilityDate['active'] == 1) { // Facility admin/bookers  can create bookings on unavailable days
                    continue;
                }
                $unavailabilityDetails[$unavailability['facilityId']][] = Carbon::parse($unavailabilityDate['from'])->format('Y-m-d');
            }
        }
        return $unavailabilityDetails;
    }

    /**
     * Update Facility Booking
     *
     * @param FacilityBooking $facilityBooking Facility booking
     * @param array $facilityBookingFormData Facility booking form data
     * @param User $user user performing action
     *
     */
    public function updateFacilityBooking(FacilityBooking $facilityBooking, array $facilityBookingFormData, User $user)
    {
        DB::beginTransaction();
        try {
            $facilityBookingInstanceData = $facilityBookingFormData['facilityUpdateInstanceDetail'];
            $facilityBookingRecurrence = $facilityBooking->facilityBookingRecurrence;
            $bookingsUpdated = collect([]);
            //Update facility bookings
            switch ($facilityBookingInstanceData['save_recurrence_type']) {
                case 'current': // Update only one instance
                    //If move use the move facility id
                    $bookingFacility = $facilityBookingFormData['facilityBookingMainData']['moved_booking'] == 1 ? Facility::find($facilityBookingFormData['facilityBookingMainData']['facility_id']) : $facilityBooking->facility;
                    $bookingDateP = Carbon::createFromFormat('d/m/Y', $facilityBookingFormData['facilityBookingMainData']['booking_date']);

                    $this->saveFacilityBooking(
                        $bookingFacility,
                        $facilityBookingRecurrence,
                        $bookingDateP,
                        $facilityBookingFormData,
                        $user,
                        $facilityBooking
                    );

                    //Update linked facilites
                    $this->updateLinkedFacility($facilityBooking, $bookingFacility, $facilityBookingRecurrence, $bookingDateP, $facilityBookingFormData, $user);
                    $bookingsUpdated->push($facilityBooking);
                    break;
                case 'date_range': // Update a date range
                    $facilityBookings =  $facilityBooking->facilityBookingRecurrence->facilityBookings()->with(['facility.location', 'linkedToFacilityBooking', 'linkedFacilityBookings.facility'])->whereBetween(
                        'FB_BookingStartDateTime',
                        [
                            Carbon::createFromFormat('d/m/Y', $facilityBookingInstanceData['save_recurrence_type_start_date_range'])->startOfDay(),
                            Carbon::createFromFormat('d/m/Y', $facilityBookingInstanceData['save_recurrence_type_end_date_range'])->endOfDay()->subSecond(1) //Database considering next day so reducing precision time by 1 second
                        ]
                    )->get();
                    $bookingFacility = $facilityBookingFormData['facilityBookingMainData']['moved_booking'] == 1 ? Facility::find($facilityBookingFormData['facilityBookingMainData']['facility_id']) : $facilityBooking->facility;
                    foreach ($facilityBookings as $facilityBookingM) {
                        //Facility sub type will be differentdifferent
                        $facilityBookingDataClone = $facilityBookingFormData;
                        if ($facilityBookingM->linkedToFacilityBooking != null) {
                            continue;
                        }

                        $this->saveFacilityBooking(
                            $bookingFacility,
                            $facilityBookingRecurrence,
                            $facilityBookingM->FB_BookingStartDateTime,
                            $facilityBookingDataClone,
                            $user,
                            $facilityBookingM
                        );

                        //Update linked facilites
                        $this->updateLinkedFacility($facilityBookingM, $bookingFacility, $facilityBookingRecurrence, $facilityBookingM->FB_BookingStartDateTime, $facilityBookingFormData, $user);
                        $bookingsUpdated->push($facilityBookingM);
                    }
                    break;
                case 'all_series': // Update all series
                    $buildQuery = $facilityBooking->facilityBookingRecurrence->facilityBookings()->whereBetween(
                        'FB_BookingStartDateTime',
                        [
                            $facilityBooking->facilityBookingRecurrence->FBR_SeriesStartDate->startOfDay(),
                            $facilityBooking->facilityBookingRecurrence->FBR_SeriesEndDate->endOfDay()->subSecond(1) //Database considering next day so reducing precision time by 1 second
                        ]
                    )->with(['facility.location', 'linkedToFacilityBooking', 'linkedFacilityBookings.facility']);
                    if (isset($facilityBookingFormData['admin_page']) && $facilityBookingFormData['admin_page'] == 1) {
                        $facilityBookings = $buildQuery->get();
                    } else {
                        $facilityBookings = $buildQuery->whereDate('FB_BookingStartDateTime', '>=', Carbon::now()->startOfDay())->get();
                    }
                    $bookingFacility = $facilityBookingFormData['facilityBookingMainData']['moved_booking'] == 1 ? Facility::find($facilityBookingFormData['facilityBookingMainData']['facility_id']) : $facilityBooking->facility;

                    foreach ($facilityBookings as $facilityBookingM) {
                        //Facility sub type will be different
                        $facilityBookingDataClone = $facilityBookingFormData;
                        if ($facilityBookingM->linkedToFacilityBooking != null) {
                            continue;
                        }

                        $this->saveFacilityBooking(
                            $bookingFacility,
                            $facilityBookingRecurrence,
                            $facilityBookingM->FB_BookingStartDateTime,
                            $facilityBookingDataClone,
                            $user,
                            $facilityBookingM
                        );

                        //Update linked facilites
                        $this->updateLinkedFacility($facilityBookingM, $bookingFacility, $facilityBookingRecurrence, $facilityBookingM->FB_BookingStartDateTime, $facilityBookingFormData, $user);
                        $bookingsUpdated->push($facilityBookingM);
                    }
                    break;
            }

            //Recurrence changes
            $differences = [];
            $changeInRecurrence = false;
            if (isset($facilityBookingFormData['facilityBookingMainData']['recurring_booking_enable'])) {
                $beforeFacilityBookingRecurrence = clone $facilityBookingRecurrence;
                $this->saveFacilityBookingRecurrence($facilityBookingRecurrence->facility, $facilityBookingFormData, $user, $facilityBookingRecurrence);

                //Check if recurrence setting is updated
                $only = $beforeFacilityBookingRecurrence->checkRecurrenceDifferenceArray();
                $differences = array_diff_assoc($beforeFacilityBookingRecurrence->only($only), $facilityBookingRecurrence->only($only));

                if (!empty($differences)) {
                    $changeInRecurrence = true;
                }
                if (!$facilityBookingRecurrence->is_recurring && !$beforeFacilityBookingRecurrence->is_recurring) { // While moving one off booking to different dates
                    $changeInRecurrence = false;
                }
            }
            //Create delete facility bookings based on recurrence
            if ($changeInRecurrence) {
                $allFutureFacilityBookings = $facilityBooking->facilityBookingRecurrence->facilityBookings()->whereDate('FB_BookingStartDateTime', '>=', Carbon::now()->startOfDay())->with('facility')->get();
                $linkedFacilities = $facilityBookingRecurrence->linkedFacilities()->with(['facilitySubTypes'])->get();
                $linkedFacilityUnavailability = $this->linkedFacilityUnvailableDates($bookingFacility, $facilityBookingFormData, $user);
                foreach ($facilityBookingRecurrence->getAvailableDates($user) as $availableDate) {
                    // Skip past dates
                    if ($availableDate->startOfDay()->lessThan(Carbon::now()->startOfDay())) {
                        continue;
                    }

                    // Check if booking already exists for this date
                    $existingBooking = $allFutureFacilityBookings->first(function ($booking) use ($availableDate) {
                        return Carbon::parse($booking->FB_BookingStartDateTime)->startOfDay()->equalTo($availableDate->startOfDay());
                    });

                    if ($existingBooking) {
                        // Booking already exists for this date, skip
                        if (in_array($existingBooking->FB_BookingStatus, [FacilityBooking::BOOKING_STATUS_CANCELLED, FacilityBooking::BOOKING_STATUS_DECLINED])) {
                            $existingBooking->FB_BookingStatus = $existingBooking->facility->FC_DefaultBookingType == Facility::DEFAULT_BOOKING_SELF_BOOKED ? FacilityBooking::BOOKING_STATUS_CONFIRMED : $facilityBookingFormData['facilityBookingMainData']['booking_status'];
                            $existingBooking->save();
                        }
                        continue;
                    }

                    // Create new booking
                    $newBooking = $this->saveFacilityBooking(
                        $facilityBookingRecurrence->facility,
                        $facilityBookingRecurrence,
                        $availableDate,
                        $facilityBookingFormData,
                        $user
                    );
                    $bookingsUpdated->push($newBooking);
                    //Add recurrence history
                    $this->addRecurrenceHistory($beforeFacilityBookingRecurrence, $facilityBookingRecurrence, $newBooking, $user);

                    // Handle linked facilities
                    $linkedBookingIds = [];
                    foreach ($linkedFacilities as $linkedFacility) {
                        //Check the date is available
                        if (isset($linkedFacilityUnavailability[$linkedFacility->FC_FacilityID]) && in_array($availableDate->format('Y-m-d'), $linkedFacilityUnavailability[$linkedFacility->FC_FacilityID])) {
                            continue;
                        }
                        //Facility sub type will be different
                        $faciltySubType = $linkedFacility->facilitySubTypes->filter(function ($item) {
                            return $item->getOriginal('pivot_FCST_PrimarySubType') == 1;
                        })->first();
                        $facilityBookingDataClone = $facilityBookingFormData;
                        $facilityBookingDataClone['facilityBookingMainData']['facility_booking_facility_sub_type_form'] = $faciltySubType->FST_FacilitySubTypeID;

                        $linkedNewBooking = $this->saveFacilityBooking(
                            $linkedFacility,
                            $facilityBookingRecurrence,
                            $availableDate,
                            $facilityBookingDataClone,
                            $user
                        );
                        //Add recurrence history
                        $this->addRecurrenceHistory($beforeFacilityBookingRecurrence, $facilityBookingRecurrence, $linkedNewBooking, $user);

                        $linkedBookingIds[] = $linkedNewBooking->FB_FacilityBookingID;
                    }

                    if (!empty($linkedBookingIds)) {
                        $newBooking->linkedFacilityBookings()->attach($linkedBookingIds);
                    }
                }

                // Delete other bookings not in available dates
                $availableDates = collect($facilityBookingRecurrence->getAvailableDates($user))->map(function ($date) {
                    return $date->startOfDay()->toDateString();
                });
                $bookingDeleted = collect([]);
                foreach ($allFutureFacilityBookings as $booking) {
                    $bookingDate = $booking->FB_BookingStartDateTime->startOfDay()->toDateString();
                    //Add recurrence history
                    $this->addRecurrenceHistory($beforeFacilityBookingRecurrence, $facilityBookingRecurrence, $booking, $user);

                    if (!in_array($bookingDate, $availableDates->toArray())) {
                        $bookingDeleted->push($booking);
                        $booking->delete();
                    }
                }
            }

            // send email
            $this->sendUpdateMail($bookingsUpdated, $user);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Send update mail
     *
     * @param Collection $bookingsUpdated bookings updated
     * @param User $user user
     */
    public function sendUpdateMail(Collection $bookingsUpdated, User $user)
    {
        //Check if declined
        $isDeclined = $bookingsUpdated->filter(function ($booking) {
            return $booking->wasChanged('FB_BookingStatus') && $booking->FB_BookingStatus == FacilityBooking::BOOKING_STATUS_DECLINED;
        })->count() > 0;

        //Check confimed
        $isConfirmed = $bookingsUpdated->filter(function ($booking) {
            return $booking->wasChanged('FB_BookingStatus') && $booking->FB_BookingStatus == FacilityBooking::BOOKING_STATUS_CONFIRMED;
        })->count() > 0;

        //Check start/end time
        $isBookingUpdated = $bookingsUpdated->filter(function ($booking) {
            return $booking->wasChanged('FB_BookingStartDateTime') || $booking->wasChanged('FB_BookingEndDateTime');
        })->count() > 0;

        if (!$isDeclined && !$isConfirmed && !$isBookingUpdated) {
            return;
        }
        //Created updated users contact
        $createdUpdatedUsers = User::query()
            ->where(function ($q) use ($bookingsUpdated) {
                $q->whereIn('UD_UserID', $bookingsUpdated->pluck('FB_CreatedBy')->toArray())
                    ->orWhereIn('UD_UserID', $bookingsUpdated->pluck('FB_UpdatedBy')->toArray());
            })->whereNotNull('UD_InternalEmail')->get();
        //Facility contacts
        $facilityContacts = $bookingsUpdated->pluck('facility')->pluck('facility_contact_emails')->flatten()->toArray();
        $linkedFacilityContancts = $bookingsUpdated->pluck('linkedFacilityBookings.*.facility.facility_contact_emails')->flatten()->toArray();
        //If contacts empty dont send email
        if (empty($linkedFacilityContancts) && empty($facilityContacts)) {
            return;
        }
        if ($createdUpdatedUsers->count() == 0) {
            return;
        }
        $facilityContacts = array_merge(
            $facilityContacts,
            $linkedFacilityContancts,
        );
        $mailObj = Mail::to($createdUpdatedUsers->pluck('UD_InternalEmail')->toArray())
            ->cc($facilityContacts);
        if ($isDeclined || $isConfirmed) { //Status update
            $mailObj->send(new FacilityBookingStatusUpdateMail($user, $bookingsUpdated, $isDeclined ? FacilityBooking::BOOKING_STATUS_DECLINED : FacilityBooking::BOOKING_STATUS_CONFIRMED));
        } elseif ($isBookingUpdated) { //Time updated
            $mailObj->send(new FacilityBookingUpdateMail($user, $bookingsUpdated));
        }
    }
    /**
     * Update linked facilites
     *
     * @param FacilityBooking $facilityBooking
     * @param Facility $facilityP
     * @param FacilityBookingRecurrence $facilityBookingRecurrence
     * @param Carbon $bookingDate
     * @param array $facilityBookingFormData
     * @param User $user
     */
    private function updateLinkedFacility(FacilityBooking $facilityBooking, Facility $facilityP, FacilityBookingRecurrence $facilityBookingRecurrence, Carbon $bookingDate, array $facilityBookingFormData, User $user)
    {
        $existingLinkedFacilities = $facilityBooking->linkedFacilityBookings;
        $linkedFacilitiesUpdate = !empty($facilityBookingFormData['facilityBookingFacilityLink']) ? Facility::whereIn('FC_FacilityID', $facilityBookingFormData['facilityBookingFacilityLink'])->with(['facilitySubTypes'])->get() : collect([]);

        $oldLinkedIds = $existingLinkedFacilities->pluck('FB_FacilityID')->toArray();
        $newLinkedIds = $facilityBookingFormData['facilityBookingFacilityLink'] ?? [];

        $added = array_diff($newLinkedIds, $oldLinkedIds);
        $removed = array_diff($oldLinkedIds, $newLinkedIds);

        //History for adding the non mandatory linked facility
        foreach ($added as $facilityId) {
            $facilityName = Facility::find($facilityId)->FC_FacilityName ?? '';
            $this->storeFacilityBookingHistory($facilityBooking, [
                __('Non-mandatory linked facility :facility added by :user on :datetime', [
                    'facility' => $facilityName,
                    'user'     => $user->UD_DisplayName,
                    'datetime' => HistoryLog::DATETIME_REPLACE_STRING,
                ])
            ], $user);
        }

        //History for removing the non mandatory linked facility
        foreach ($removed as $facilityId) {
            $facilityName = Facility::find($facilityId)->FC_FacilityName ?? '';
            $this->storeFacilityBookingHistory($facilityBooking, [
                __('Non-mandatory linked facility :facility removed by :user on :datetime', [
                    'facility' => $facilityName,
                    'user'     => $user->UD_DisplayName,
                    'datetime' => HistoryLog::DATETIME_REPLACE_STRING,
                ])
            ], $user);
        }

        //Create new bookings
        $newBookingIds = [];
        $linkedFacilityUnavailability = $this->linkedFacilityUnvailableDates($facilityP, $facilityBookingFormData, $user);
        foreach ($linkedFacilitiesUpdate as $newLinkedFacility) {
            if ($existingLinkedFacilities->where('FB_FacilityID', $newLinkedFacility->FC_FacilityID)->count() > 0) {
                continue;
            }
            //Check the date is available
            if (isset($linkedFacilityUnavailability[$newLinkedFacility->FC_FacilityID]) && in_array($bookingDate->format('Y-m-d'), $linkedFacilityUnavailability[$newLinkedFacility->FC_FacilityID])) {
                continue;
            }
            //Facility sub type will be different
            $facilityBookingDataLinked = $facilityBookingFormData;
            $facilityBookingDataLinked['facilityBookingMainData']['facility_booking_facility_sub_type_form'] = $newLinkedFacility->facilitySubTypes
                ->filter(function ($value, $key) {
                    return $value->getOriginal('pivot_FCST_PrimarySubType') == 1;
                })->first()->FB_FacilitySubTypeID;

            //Create booking
            $newBooking = $this->saveFacilityBooking(
                $newLinkedFacility,
                $facilityBookingRecurrence,
                $bookingDate,
                $facilityBookingDataLinked,
                $user
            );
            $newBookingIds[] = $newBooking->FB_FacilityBookingID;
        }
        !empty($newBookingIds) ? $facilityBooking->linkedFacilityBookings()->attach($newBookingIds) : '';

        //Update existing
        foreach ($existingLinkedFacilities as $linkedFacilityBooking) {
            //Linked facility exists in form
            if ($linkedFacilitiesUpdate->where('FC_FacilityID', $linkedFacilityBooking->FB_FacilityID)->count() == 0) {
                $linkedFacilityBooking->delete();
                continue;
            }

            //Facility sub type will be different
            $facilityBookingDataLinked = $facilityBookingFormData;
            $facilityBookingDataLinked['facilityBookingMainData']['facility_booking_facility_sub_type_form'] = $linkedFacilityBooking->FB_FacilitySubTypeID;

            $this->saveFacilityBooking(
                $linkedFacilityBooking->facility,
                $facilityBookingRecurrence,
                $bookingDate,
                $facilityBookingDataLinked,
                $user,
                $linkedFacilityBooking
            );
        }
    }

    /**
     * Store Recurrence history
     *
     * @param FacilityBookingRecurrence $oldRecurrence
     * @param FacilityBookingRecurrence $newRecurrence
     * @param FacilityBooking $facilityBooking
     * @param User $user
     */
    private function addRecurrenceHistory(FacilityBookingRecurrence $oldRecurrence, FacilityBookingRecurrence $newRecurrence, FacilityBooking $facilityBooking, User $user)
    {
        $recurrenceHistory = [];
        $recurrenceHistory[] = __(
            'Recurrence Settings Updated From :old To :new by :user on :datetime',
            [
                'old' =>  $oldRecurrence->recurrence_setting_string,
                'new' => $newRecurrence->recurrence_setting_string,
                'user' => $user->UD_DisplayName,
                'datetime' => HistoryLog::DATETIME_REPLACE_STRING
            ]
        );
        $this->storeFacilityBookingHistory($facilityBooking, $recurrenceHistory, $user);
    }

    /**
     * Save Facility booking
     * @param Facility $facility
     * @param FacilityBookingRecurrence $facilityBookingRecurrence
     * @param Carbon $availableDate
     * @param array $facilityBookingData
     * @param User $user
     * @param FacilityBooking $existingFacilityBooking
     */
    private function saveFacilityBooking(Facility $facility, FacilityBookingRecurrence $facilityBookingRecurrence, Carbon $availableDate, array $facilityBookingData, User $user, ?FacilityBooking $existingFacilityBooking = null)
    {
        $historyData = [];
        $facilityBookingMainData = $facilityBookingData['facilityBookingMainData'];

        $history = [];

        $facilityBooking = $existingFacilityBooking == null ? new FacilityBooking() : $existingFacilityBooking;

        //Facility Booking RecurrenceID
        $facilityBooking->FB_FacilityBookingRecurrenceID = $facilityBookingRecurrence->FBR_FacilityBookingRecurrenceID;

        //Facility ID
        $facilityBooking->FB_FacilityID = $facility->FC_FacilityID;

        //Storing facilityID for
        $existingFacility = $facility->FC_FacilityID;


        //Start Date Time
        $facilityBooking->FB_BookingStartDateTime = $availableDate->format('Y-m-d') . ' ' . $facilityBookingMainData['booking_start_time'];

        //History Booking Start Time
        if ($existingFacilityBooking != null) {
            if ($facilityBooking->FB_BookingStartDateTime->format('Y-m-d H:i') !=  $facilityBooking->getOriginal('FB_BookingStartDateTime')->format('Y-m-d H:i')) {
                $history[] = __('Booking Start Time changed from :old_value to :new_value', ['old_value' => ($facilityBooking->getOriginal('FB_BookingStartDateTime')->format('d/m/Y H:i')), 'new_value' => ($facilityBooking->FB_BookingStartDateTime)->format('d/m/Y H:i')]);
            }
        }

        //End Date Time
        $startTime = Carbon::createFromFormat('H:i', $facilityBookingMainData['booking_start_time']);
        $endTime = Carbon::createFromFormat('H:i', $facilityBookingMainData['booking_end_time']);
        $bookingEndDate = clone $availableDate;
        if ($endTime->lte($startTime)) { // End date will be next day
            $bookingEndDate = $bookingEndDate->addDay();
        }
        $facilityBooking->FB_BookingEndDateTime = $bookingEndDate->format('Y-m-d')  . ' ' . $facilityBookingMainData['booking_end_time'];

        //History Booking End Time
        if ($existingFacilityBooking != null) {
            if ($facilityBooking->FB_BookingEndDateTime->format('Y-m-d H:i') !=  $facilityBooking->getOriginal('FB_BookingEndDateTime')->format('Y-m-d H:i')) {
                $history[] = __('Booking End Time changed from :old_value to :new_value', ['old_value' => ($facilityBooking->getOriginal('FB_BookingEndDateTime')->format('d/m/Y H:i')), 'new_value' => ($facilityBooking->FB_BookingEndDateTime)->format('d/m/Y H:i')]);
            }
        }

        //Booking Title
        $facilityBooking->FB_BookingTitle = $facilityBookingMainData['facility_request_booking_title'];

        //History Booking Title
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_BookingTitle')) {
            $history[] = __('Booking Title changed from :old_value to :new_value', ['old_value' => $facilityBooking->getOriginal('FB_BookingTitle'), 'new_value' =>  $facilityBooking->FB_BookingTitle]);
        }

        //Facility sub type
        $facilityBooking->FB_FacilitySubTypeID = $facilityBookingMainData['facility_booking_facility_sub_type_form'];

        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_FacilitySubTypeID')) {
            $facilitySubTypeHistory = FacilitySubType::whereIn('FST_FacilitySubTypeID', [$facilityBooking->getOriginal('FB_FacilitySubTypeID'), $facilityBooking->FB_FacilitySubTypeID])->get();
            $history[] = __('Facility Sub Type changed from :old_value to :new_value', [
                'old_value' => ($facilitySubTypeHistory->where('FST_FacilitySubTypeID', $facilityBooking->getOriginal('FB_FacilitySubTypeID'))->count() > 0 ? $facilitySubTypeHistory->where('FST_FacilitySubTypeID', $facilityBooking->getOriginal('FB_FacilitySubTypeID'))->first()->FST_FacilitySubType : ''),
                'new_value' => $facilitySubTypeHistory->where('FST_FacilitySubTypeID', $facilityBooking->FB_FacilitySubTypeID)->first()->FST_FacilitySubType
            ]);
        }

        //Private
        $facilityBooking->FB_Private = $facilityBookingMainData['private_booking'];
        //History Private Booking
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_Private')) {
            $history[] = __('Private Booking Type changed from :old_value to :new_value', ['old_value' => ucfirst($facilityBooking->getOriginal('FB_Private')), 'new_value' => ucfirst($facilityBooking->FB_Private)]);
        }

        //Customer type
        $facilityBooking->FB_CustomerType = $facilityBookingMainData['customer_type'];

        //History Customer Type
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_CustomerType')) {
            $history[] = __('Customer Type changed from :old_value to :new_value', ['old_value' => $facilityBooking->getOriginal('FB_CustomerType'), 'new_value' =>  $facilityBooking->FB_CustomerType]);
        }

        //Company Name ID
        if ($facilityBookingMainData['customer_type'] == FacilityBookingRecurrence::CUSTOMER_TYPE_EXTERNAL) {
            $facilityBooking->FB_ExternalCustomerID = $facilityBookingMainData['external_customer_company'];
        }

        //History Company Name
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_ExternalCustomerID')) {
            $externalCustomerHistory = ExternalCustomer::withTrashed()->whereIn('EC_ExternalCustomerID', [$facilityBooking->getOriginal('FB_ExternalCustomerID'), $facilityBooking->FB_ExternalCustomerID])->get();
            $history[] = __('Company Name changed from :old_value to :new_value', [
                'old_value' => !empty($facilityBooking->getOriginal('FB_ExternalCustomerID')) ? $externalCustomerHistory->where('EC_ExternalCustomerID', $facilityBooking->getOriginal('FB_ExternalCustomerID'))->first()->EC_CompanyName : '',
                'new_value' => !empty($facilityBooking->FB_ExternalCustomerID) ? $externalCustomerHistory->where('EC_ExternalCustomerID', $facilityBooking->FB_ExternalCustomerID)->first()->EC_CompanyName : ''
            ]);
        }

        //Customer details
        $facilityBooking->FB_ContactName = $facilityBookingMainData['customer_contact_name'] ?? null;

        //History External Customer Contact Name
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_ContactName')) {
            $history[] = __('Customer Contact changed from :old_value to :new_value', ['old_value' => $facilityBooking->getOriginal('FB_ContactName'), 'new_value' =>  $facilityBooking->FB_ContactName]);
        }

        $facilityBooking->FB_ContactTelephone = $facilityBookingMainData['customer_contact_telephone'] ?? null;
        $facilityBooking->FB_ContactEmail = $facilityBookingMainData['customer_contact_email'] ?? null;
        $facilityBooking->FB_RequestorName = $facilityBookingMainData['requestor_name'];
        $facilityBooking->FB_RequestorDetail = $facilityBookingMainData['requestor_detail'];
        $facilityBooking->FB_RequestorNote = $facilityBookingMainData['requestor_notes'];
        $facilityBooking->FB_SchedulerNote = $facilityBookingMainData['scheduler_notes'];

        // Scheduler Note
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_SchedulerNote')) {
            $oldValue = $facilityBooking->getOriginal('FB_SchedulerNote');
            $newValue = $facilityBooking->FB_SchedulerNote;

            if (empty($oldValue) && !empty($newValue)) {
                $history[] = __('Scheduler Note updated as :new_value', ['new_value' => $newValue]);
            } elseif (!empty($oldValue) && empty($newValue)) {
                $history[] = __('Scheduler Note removed :old_value', ['old_value' => $oldValue]);
            } elseif (!empty($oldValue) && !empty($newValue)) {
                $history[] = __('Scheduler Note updated :old_value to :new_value', ['old_value' => $oldValue, 'new_value' => $newValue]);
            }
        }


        // Requestor Note
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_RequestorNote')) {
            $history[] = __('Requestor Note changed from :old_value to :new_value', ['old_value' => $facilityBooking->getOriginal('FB_RequestorNote'), 'new_value' =>  $facilityBooking->FB_RequestorNote]);
        }

        // Requestor Detail
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_RequestorDetail')) {
            $history[] = __('Requestor Detail changed from :old_value to :new_value', ['old_value' => $facilityBooking->getOriginal('FB_RequestorDetail'), 'new_value' =>  $facilityBooking->FB_RequestorDetail]);
        }

        //History External Customer Contact  Phone
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_ContactTelephone')) {
            $history[] = __('Contact Telephone changed from :old_value to :new_value', ['old_value' => $facilityBooking->getOriginal('FB_ContactTelephone'), 'new_value' =>  $facilityBooking->FB_ContactTelephone]);
        }

        //History External Customer Requestor Name
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_RequestorName')) {
            $history[] = __('Requestor Name changed from :old_value to :new_value', ['old_value' => $facilityBooking->getOriginal('FB_RequestorName'), 'new_value' =>  $facilityBooking->FB_RequestorName]);
        }

        //History External Customer Contact  Phone
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_ContactEmail')) {
            $history[] = __('Contact Email changed from :old_value to :new_value', ['old_value' => $facilityBooking->getOriginal('FB_ContactEmail'), 'new_value' =>  $facilityBooking->FB_ContactEmail]);
        }


        //  History for moving facility from source to destination
        if ($existingFacility != null && $existingFacilityBooking != null && $facilityBooking->isDirty('FB_FacilityID')) {
            $sourceFacilityId = $facilityBooking->getOriginal('FB_FacilityID');
            $destinationFacilityId = $facilityBooking->FB_FacilityID;

            // Safely fetch names (avoid nulls if facility was archived/deleted)
            $sourceFacilityName = Facility::find($sourceFacilityId)->FC_FacilityName ?? (string)$sourceFacilityId;
            $destinationFacilityName = Facility::find($destinationFacilityId)->FC_FacilityName ?? (string)$destinationFacilityId;

            $historyData[] = __(
                'Facility Booking moved from :source to :destination by :user on :datetime',
                [
                    'source'     => $sourceFacilityName,
                    'destination' => $destinationFacilityName,
                    'user'       => $user->UD_DisplayName,
                    'datetime'   => HistoryLog::DATETIME_REPLACE_STRING,
                ]
            );
        }

        if ($facilityBooking->FB_CreatedBy == null) {
            $facilityBooking->FB_CreatedBy = $user->UD_UserID;
        }

        $facilityBooking->FB_UpdatedBy = $user->UD_UserID;

        //Booking status
        $facilityBooking->FB_BookingStatus = $facilityBookingMainData['booking_status'] ?? FacilityBooking::BOOKING_STATUS_NEW;
        if ($facilityBooking->FB_BookingStatus == FacilityBooking::BOOKING_STATUS_DECLINED && $facility->FC_DefaultBookingType == Facility::DEFAULT_BOOKING_SELF_BOOKED) {
            $facilityBooking->FB_BookingStatus = FacilityBooking::BOOKING_STATUS_CANCELLED; // Self booked facility can have only confirmed and cancelled status
        } elseif ($facility->FC_DefaultBookingType == Facility::DEFAULT_BOOKING_SELF_BOOKED) {
            $facilityBooking->FB_BookingStatus = FacilityBooking::BOOKING_STATUS_CONFIRMED;
        }

        if (isset($facilityBookingMainData['booking_status']) && $facilityBookingMainData['booking_status'] == FacilityBooking::BOOKING_STATUS_CONFIRMED) {
            $facilityBooking->FB_BookingConfirmDate = Carbon::now()->format('Y-m-d H:i');
        }
        if (isset($facilityBookingMainData['booking_status']) && $facilityBookingMainData['booking_status'] == FacilityBooking::BOOKING_STATUS_DECLINED) {
            $facilityBooking->FB_BookingDeclineDate = Carbon::now()->format('Y-m-d H:i');
            $facilityBooking->FB_BookingDeclineReason = $facilityBookingData['facilityUpdateInstanceDetail']['declined_reason'] ?? null;
        }

        //History Facility Booking Status
        if ($existingFacilityBooking != null && $facilityBooking->isDirty('FB_BookingStatus')) {
            $history[] = __('Booking Status changed from :old_value to :new_value', ['old_value' => $facilityBooking->getOriginal('FB_BookingStatus'), 'new_value' =>  $facilityBooking->FB_BookingStatus]);
            if (
                ($facilityBooking->FB_BookingStatus == FacilityBooking::BOOKING_STATUS_DECLINED || $facilityBooking->FB_BookingStatus == FacilityBooking::BOOKING_STATUS_CANCELLED)
                && $facilityBookingData['facilityUpdateInstanceDetail']['declined_reason'] != ''
            ) {
                $history[] = __(" with reason: :declineReason", ['declineReason' => $facilityBookingData['facilityUpdateInstanceDetail']['declined_reason']]);
            }
        }

        if (!empty($history)) {
            $historyData[] = __(
                ':changed by :user on :datetime',
                [
                    'changed' => implode(', ', $history),
                    'user' => $user->UD_DisplayName,
                    'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                ]
            );
        }

        $facilityBooking->save();

        //Copy history
        if ($facilityBookingMainData['copy_booking_id'] != null) {
            $copiedFromFacility = FacilityBooking::find($facilityBookingMainData['copy_booking_id']);
            $historyLogs = $copiedFromFacility->history;
            foreach ($historyLogs as $historyLogsList) {
                $history_new = $historyLogsList->replicate();
                $history_new['HL_AttributeID'] = $facilityBooking->FB_FacilityBookingID;
                $history_new['HL_Created_BY'] = $historyLogsList['HL_Created_BY'];
                $history_new['HL_Created_ON'] = $historyLogsList['HL_Created_ON'];
                $history_new->save();
            }
        }

        if ($existingFacilityBooking == null && $facilityBookingMainData['copy_booking_id'] == null) { // create facility history
            $historyData[] = __('Facility Booking created by :user on :datetime', [
                'user' => $user->UD_DisplayName,
                'datetime' => HistoryLog::DATETIME_REPLACE_STRING
            ]);
        } elseif ($facilityBookingMainData['copy_booking_id'] != null) {
            $historyData[] = __('Facility Booking copied by :user on :datetime', [
                'user' => $user->UD_DisplayName,
                'datetime' => HistoryLog::DATETIME_REPLACE_STRING
            ]);
        }

        //Actions
        $history = $existingFacilityBooking != null ? $this->bookingActionsHistory($facilityBooking, collect($facilityBookingData['facilityBookingActions'] ?? [])) : [];
        if (!empty($history)) {
            $historyData[] = __('Action changed from :data1 to :data2 by :user on :datetime', [
                'data1' => $history['from']->implode(', '),
                'data2' => $history['to']->implode(', '),
                'user' => $user->UD_DisplayName,
                'datetime' => HistoryLog::DATETIME_REPLACE_STRING
            ]);
        }
        $actions = [];
        foreach ($facilityBookingData['facilityBookingActions'] ?? [] as $id => $actionData) {
            $actions[$id] = [
                'FBA_ActionStartTime' => $actionData['actionStartTime'],
                'FBA_ActionEndTime' => $actionData['actionEndTime']
            ];
        }
        $facilityBooking->actions()->sync($actions);
        $facilityBooking->save();

        //Save history
        $this->storeFacilityBookingHistory($facilityBooking, $historyData, $user);

        return $facilityBooking;
    }

    /**
     * Generate actions history data
     * @param FacilityBooking $editedFacilityBooking
     * @param Collection $requestData
     */
    private function bookingActionsHistory(FacilityBooking $editedFacilityBooking, Collection $requestData): array
    {
        $existingActions = $editedFacilityBooking->actions;
        $stringMaker = function ($name, $startTime, $endTime) {
            return $name . ' (' . substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5) . ')';
        };
        $existingData = [];
        foreach ($existingActions as $existingAction) {
            $existingData[] = $stringMaker($existingAction->action_name, $existingAction->getOriginal('pivot_FBA_ActionStartTime'), $existingAction->getOriginal('pivot_FBA_ActionEndTime'));
        }
        $newData = [];
        $newActions = Action::whereIn('action_id', $requestData->pluck('action_id')->toArray())->get();
        foreach ($newActions as $newAction) {
            $newActionData = $requestData->where('action_id', $newAction->action_id)->first();
            $newData[] = $stringMaker($newAction->action_name, $newActionData['actionStartTime'], $newActionData['actionEndTime']);
        }
        $existingData = collect($existingData);
        $newData = collect($newData);
        return ($existingData->diff($newData)->count() > 0 || $newData->diff($existingData)->count()) ? ['from' => $existingData, 'to' => $newData] : [];
    }

    /**
     * Store Facility Booker Note Recurrence
     *
     * @param Facility $facility Facility
     * @param array $facilityBookerNoteData Facility booker note data
     * @param User $user user performing action
     * @param FacilityBookerNoteRecurrence $editFacilityBookerNoteRecurrence edit recurrence
     *
     */
    public function saveFacilityBookerNoteRecurrence(Facility $facility, array $facilityBookerNoteData, User $user, ?FacilityBookerNoteRecurrence $editFacilityBookerNoteRecurrence = null)
    {
        DB::beginTransaction();
        try {
            $facilityBookerNoteMainData = $facilityBookerNoteData['facilityBookerNoteData'];
            $facilityBookerNoteRecurrence = $editFacilityBookerNoteRecurrence == null ? new FacilityBookerNoteRecurrence() : $editFacilityBookerNoteRecurrence;

            //Facility id
            $facilityBookerNoteRecurrence->FBNR_FacilityID = $facility->FC_FacilityID;

            //Note
            $facilityBookerNoteRecurrence->FBNR_Note = $facilityBookerNoteMainData['booker_note_frm'];

            //Series Start date
            $bookingDate = Carbon::createFromFormat('d/m/Y', $facilityBookerNoteMainData['booker_note_date']);

            $startDate = $facilityBookerNoteMainData['recurring_booker_note_enable'] == 'yes' ? Carbon::createFromFormat('d/m/Y', $facilityBookerNoteMainData['booker_note_recurring_start_date']) : $bookingDate;
            $facilityBookerNoteRecurrence->FBNR_SeriesStartDate = $startDate->format('Y-m-d');

            //Series End date
            $endDate = $facilityBookerNoteMainData['recurring_booker_note_enable'] == 'yes' ? Carbon::createFromFormat('d/m/Y', $facilityBookerNoteMainData['booker_note_recurring_end_date']) : $bookingDate;
            $facilityBookerNoteRecurrence->FBNR_SeriesEndDate = $endDate->format('Y-m-d');

            //Start Time
            $facilityBookerNoteRecurrence->FBNR_StartTime = $facilityBookerNoteMainData['booker_note_start_time'];

            //End Time
            $facilityBookerNoteRecurrence->FBNR_EndTime = $facilityBookerNoteMainData['booker_note_end_time'];

            //Appointment details
            if ($facilityBookerNoteMainData['recurring_booker_note_enable'] == 'yes') {
                $facilityBookerNoteRecurrence->FBNR_RecurrenceType = $facilityBookerNoteMainData['booker_note_recurring_recurrence_type'];
                if ($facilityBookerNoteRecurrence->FBNR_RecurrenceType == FacilityBookingRecurrence::RECUR_TYPE_DAILY) {
                    $facilityBookerNoteRecurrence->FBNR_RecurrenceDayInterval = $facilityBookerNoteMainData['recurring_booker_note_recurrence_daily_days'];
                } elseif ($facilityBookerNoteRecurrence->FBNR_RecurrenceType == FacilityBookingRecurrence::RECUR_TYPE_WEEKLY) {
                    $facilityBookerNoteRecurrence->FBNR_RecurrenceWeekInterval = $facilityBookerNoteMainData['recurring_booker_note_recurrence_weekly_weeks'];
                    $days = ['Sunday', 'Saturday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                    foreach ($days as $dayLoop) { //Reset
                        $facilityBookerNoteRecurrence->{'FBNR_RecurrenceWeek_' . $dayLoop} = 0;
                    }
                    foreach ($facilityBookerNoteMainData['recurring_booker_note_weekly_days'] as $day) {
                        $facilityBookerNoteRecurrence->{'FBNR_RecurrenceWeek_' . ucfirst($day)} = 1;
                    }
                }
            } else {
                $facilityBookerNoteRecurrence->FBNR_RecurrenceType = FacilityBookingRecurrence::RECUR_TYPE_DAILY;
                $facilityBookerNoteRecurrence->FBNR_RecurrenceDayInterval = 1;
            }

            $facilityBookerNoteRecurrence->FBNR_CreatedBy = $user->UD_UserID;
            $facilityBookerNoteRecurrence->FBNR_UpdatedBy = $user->UD_UserID;
            $facilityBookerNoteRecurrence->save();

            if ($editFacilityBookerNoteRecurrence == null) {
                $schedulerAvailableDates = $facilityBookerNoteRecurrence->getAvailableDates();
                if ($facilityBookerNoteMainData['recurring_booker_note_enable'] == 'yes' && $bookingDate != $startDate) {
                    $dateExist = 0;
                    foreach ($schedulerAvailableDates as $availableDate) {
                        if ($availableDate->format('Y-m-d') == $bookingDate->format('Y-m-d')) {
                            $dateExist = 1;
                        }
                    }
                    if ($dateExist == 0) {
                        $schedulerAvailableDates[] = $bookingDate;
                    }
                }
                //Create booker notes
                foreach ($schedulerAvailableDates as $availableDate) {
                    $this->saveFacilityBookerNote($facility, $facilityBookerNoteRecurrence, $availableDate, $facilityBookerNoteData, $user);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update facility booker note
     *
     * @param FacilityBookerNote $facilityBookerNote
     * @param array $facilityBookerNoteData
     * @param User $user
     */
    public function updateFacilityBookerNote(FacilityBookerNote $facilityBookerNote, array $facilityBookerNoteData, User $user)
    {
        DB::beginTransaction();
        try {
            $facilityBookerNoteMainData = $facilityBookerNoteData['facilityBookerNoteData'];
            $facilityBookerNoteRecurrence = $facilityBookerNote->facilityBookerNoteRecurrence;
            switch ($facilityBookerNoteMainData['booker_note_save_recurrence_type']) {
                case 'current': // Update only one instance
                    $this->saveFacilityBookerNote(
                        $facilityBookerNote->facility,
                        $facilityBookerNote->facilityBookerNoteRecurrence,
                        Carbon::createFromFormat('d/m/Y', $facilityBookerNoteMainData['booker_note_date']),
                        $facilityBookerNoteData,
                        $user,
                        $facilityBookerNote
                    );
                    break;
                case 'date_range': // Updagte a date range
                    $facilityBookerNotes =  $facilityBookerNote->facilityBookerNoteRecurrence->facilityBookerNotes()->with('facility')->whereBetween(
                        'FBN_StartDateTime',
                        [
                            Carbon::createFromFormat('d/m/Y', $facilityBookerNoteMainData['booker_note_save_recurrence_type_start_date_range'])->startOfDay(),
                            Carbon::createFromFormat('d/m/Y', $facilityBookerNoteMainData['booker_note_save_recurrence_type_end_date_range'])->endOfDay()
                        ]
                    )->get();
                    foreach ($facilityBookerNotes as $facilityBookerNoteM) {
                        $this->saveFacilityBookerNote(
                            $facilityBookerNoteM->facility,
                            $facilityBookerNoteRecurrence,
                            $facilityBookerNoteM->FBN_StartDateTime->startOfDay(),
                            $facilityBookerNoteData,
                            $user,
                            $facilityBookerNoteM
                        );
                    }
                    break;
                case 'all_series': // Update all series
                    $facilityBookerNotes =  $facilityBookerNote->facilityBookerNoteRecurrence->facilityBookerNotes()->with('facility')->get();
                    foreach ($facilityBookerNotes as $facilityBookerNoteM) {
                        $this->saveFacilityBookerNote(
                            $facilityBookerNoteM->facility,
                            $facilityBookerNoteRecurrence,
                            $facilityBookerNoteM->FBN_StartDateTime->startOfDay(),
                            $facilityBookerNoteData,
                            $user,
                            $facilityBookerNoteM
                        );
                    }
                    break;
            }

            //Create delete facility bookings based on recurrence
            $beforeFacilityBookerNoteRecurrence = clone $facilityBookerNoteRecurrence;
            $this->saveFacilityBookerNoteRecurrence($facilityBookerNote->facility, $facilityBookerNoteData, $user, $facilityBookerNoteRecurrence);

            //Check if recurrence setting is updated
            $only = $beforeFacilityBookerNoteRecurrence->checkRecurrenceDifferenceArray();
            $differences = array_diff_assoc($beforeFacilityBookerNoteRecurrence->only($only), $facilityBookerNoteRecurrence->only($only));
            if (!empty($differences)) {
                $allFutureFacilityBookerNotes = $facilityBookerNote->facilityBookerNoteRecurrence->facilityBookerNotes()->whereDate('FBN_StartDateTime', '>=', Carbon::now()->startOfDay())->with('facility')->get();
                foreach ($facilityBookerNoteRecurrence->getAvailableDates() as $availableDate) {
                    // Skip past dates
                    if ($availableDate->startOfDay()->lessThan(Carbon::now()->startOfDay())) {
                        continue;
                    }

                    // Check if booker note already exists for this date
                    $existingBookerNote = $allFutureFacilityBookerNotes->first(function ($bookerNote) use ($availableDate) {
                        return Carbon::parse($bookerNote->FBN_StartDateTime)->startOfDay()->equalTo($availableDate->startOfDay());
                    });

                    if ($existingBookerNote) {
                        // Booker note already exists for this date, skip
                        continue;
                    }

                    // Create new booker note
                    $this->saveFacilityBookerNote(
                        $facilityBookerNote->facility,
                        $facilityBookerNoteRecurrence,
                        $availableDate,
                        $facilityBookerNoteData,
                        $user
                    );
                }

                // Delete other booker note not in available dates
                $availableDates = collect($facilityBookerNoteRecurrence->getAvailableDates())->map(function ($date) {
                    return $date->startOfDay()->toDateString();
                });
                foreach ($allFutureFacilityBookerNotes as $bookerNote) {
                    $bookerNoteDate = $bookerNote->FBN_StartDateTime->startOfDay()->toDateString();
                    if (!in_array($bookerNoteDate, $availableDates->toArray())) {
                        $bookerNote->delete(); // Delete booker note in available dates
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Save facility booker note
     *
     * @param Facility $facility
     * @param FacilityBookerNoteRecurrence $facilityBookerNoteRecurrence
     * @param Carbon $availableDate
     * @param array $facilityBookerNoteMainData
     * @param User $user
     * @param FacilityBookerNote $existingFacilityBookerNote
     */
    public function saveFacilityBookerNote(Facility $facility, FacilityBookerNoteRecurrence $facilityBookerNoteRecurrence, Carbon $availableDate, array $facilityBookerNoteMainData, User $user, ?FacilityBookerNote $existingFacilityBookerNote = null)
    {
        $historyData = [];
        $facilityBookerNoteMainData = $facilityBookerNoteMainData['facilityBookerNoteData'];

        $facilityBookerNote = $existingFacilityBookerNote == null ? new FacilityBookerNote() : $existingFacilityBookerNote;

        //Facility Booking RecurrenceID
        $facilityBookerNote->FBN_FacilityBookerNoteRecurrenceID = $facilityBookerNoteRecurrence->FBNR_FacilityBookerNoteRecurrenceID;

        //Facility ID
        $facilityBookerNote->FBN_FacilityID = $facility->FC_FacilityID;

        //Start Date Time
        $facilityBookerNote->FBN_StartDateTime = $availableDate->format('Y-m-d') . ' ' . $facilityBookerNoteMainData['booker_note_start_time'];

        //End Date Time
        $startTime = Carbon::createFromFormat('H:i', $facilityBookerNoteMainData['booker_note_start_time']);
        $endTime = Carbon::createFromFormat('H:i', $facilityBookerNoteMainData['booker_note_end_time']);
        $bookerNoteEndDate = clone $availableDate;
        if ($endTime->lte($startTime)) { // End date will be next day
            $bookerNoteEndDate = $bookerNoteEndDate->addDay();
        }
        $facilityBookerNote->FBN_EndDateTime = $bookerNoteEndDate->format('Y-m-d')  . ' ' . $facilityBookerNoteMainData['booker_note_end_time'];

        //Time history
        if (
            !empty($existingFacilityBookerNote) && (
            $facilityBookerNote->getOriginal('FBN_StartDateTime')->format('Y-m-d H:i') != $facilityBookerNote->FBN_StartDateTime->format('Y-m-d H:i')
            ||
            $facilityBookerNote->getOriginal('FBN_EndDateTime')->format('Y-m-d H:i') != $facilityBookerNote->FBN_EndDateTime->format('Y-m-d H:i')
            )
        ) {
            $historyData[] = __('Scheduler Note Time changed from :date1 - :date2 to :date3 - :date4 by :user on :datetime', [
                'date1' => $facilityBookerNote->getOriginal('FBN_StartDateTime')->format('d/m/Y H:i'),
                'date2' => $facilityBookerNote->getOriginal('FBN_EndDateTime')->format('d/m/Y H:i'),
                'date3' => $facilityBookerNote->FBN_StartDateTime->format('d/m/Y H:i'),
                'date4' => $facilityBookerNote->FBN_EndDateTime->format('d/m/Y H:i'),
                'user' => $user->UD_DisplayName,
                'datetime' => HistoryLog::DATETIME_REPLACE_STRING
            ]);
        }

        //Booking Title
        $facilityBookerNote->FBN_Note = $facilityBookerNoteMainData['booker_note_frm'];

        //Note history
        if (!empty($existingFacilityBookerNote) && $facilityBookerNote->isDirty('FBN_Note')) {
            $historyData[] = __('Scheduler Note changed from :old  to :new by :user on :datetime', [
                'old' => $facilityBookerNote->getOriginal('FBN_Note'),
                'new' => $facilityBookerNote->FBN_Note,
                'user' => $user->UD_DisplayName,
                'datetime' => HistoryLog::DATETIME_REPLACE_STRING
            ]);
        }

        $facilityBookerNote->FBN_CreatedBy = $user->UD_UserID;
        $facilityBookerNote->FBN_UpdatedBy = $user->UD_UserID;

        if (empty($existingFacilityBookerNote)) {
            $historyData[] = __('Scheduler Note Added by :user on :datetime', [
                'user' => $user->UD_DisplayName,
                'datetime' => HistoryLog::DATETIME_REPLACE_STRING
            ]);
        }

        $facilityBookerNote->save();

        //Store history
        $this->storeFacilityBookerNoteHistory($facilityBookerNote, $historyData, $user);
    }

    /**
     * Reinstate Facility booking
     *
     * @param FacilityBooking $existingFacilityBooking
     */
    public function reinstateFacilityBookingData(FacilityBooking $facilityBookingP, array $facilityBookingFormData, User $user)
    {
        $facilityBookings = collect([]);
        $initQuery = $facilityBookingP->facilityBookingRecurrence->availableFacilityBookings()->with(['facility', 'linkedFacilityBookings.facility', 'linkedToFacilityBooking.facility'])
            ->where('FB_BookingStatus', \App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_CANCELLED);
        switch ($facilityBookingFormData['reinstate_booking']) {
            case 'some_booking':
                $facilityBookings = $initQuery->whereIn('FB_FacilityBookingID', $facilityBookingFormData['some_booking_list'])->get();
                break;
            case 'recurrence_booking':
                $facilityBookings = $initQuery->whereDate('FB_BookingStartDateTime', '>=', Carbon::createFromFormat('d/m/Y', $facilityBookingFormData['reinstate_booking_from_date']))
                    ->whereDate('FB_BookingStartDateTime', '<=', Carbon::createFromFormat('d/m/Y', $facilityBookingFormData['reinstate_booking_to_date']))->get();
                break;
            case 'entire_booking':
                $facilityBookings = $initQuery->get();
                break;
        }
        foreach ($facilityBookings as $facilityBooking) {
            $this->reinstateBookingInstance($facilityBooking, $user, true);
            //Reinstance linked booking
            foreach ($facilityBooking->linkedFacilityBookings as $linkedBookings) {
                $this->reinstateBookingInstance($linkedBookings, $user);
            }
            //Reinstate parent booking
            $parentBooking = $facilityBooking->linkedToFacilityBooking;
            if ($parentBooking != null && $parentBooking->FB_BookingStatus == FacilityBooking::BOOKING_STATUS_CANCELLED) {
                $this->reinstateBookingInstance($parentBooking, $user);
                //Reinstance linked booking
                foreach ($parentBooking->linkedFacilityBookings as $linkedBookings) {
                    $this->reinstateBookingInstance($linkedBookings, $user);
                }
            }
        }

        if (isset($facilityBooking)) {
            Mail::to($facilityBooking->createdBy->UD_InternalEmail)
                ->cc(array_merge([$user->UD_InternalEmail], $facilityBooking->facility->facility_contact_emails))
                ->send(new FacilityBookingReinstateMail($facilityBookings));
        }
    }

    /**
     * Reinstate Booking instance
     *
     * @param FacilityBooking $facilityBooking
     * @param User $user
     */
    function reinstateBookingInstance(FacilityBooking $facilityBooking, User $user, $historyLog = false)
    {
        $facilityBooking->FB_BookingStatus = $facilityBooking->FB_BookingPreviousStatus;
        $facilityBooking->FB_BookingCancelDate = Carbon::now();
        $facilityBooking->FB_UpdatedBy = $user->UD_UserID;
        $facilityBooking->save();
        //Save history
        if ($historyLog) {
            $historyData[] = __('Booking Reinstated by :user on :datetime', [
                'user' => $user->UD_DisplayName,
                'datetime' => HistoryLog::DATETIME_REPLACE_STRING
            ]);
            $this->storeFacilityBookingHistory($facilityBooking, $historyData, $user);
        }
    }



    public function declineConflictBookings(array $bookingIds, User $actor): Collection
    {
        $ids = array_values(array_unique(array_filter($bookingIds, fn($id) => !empty($id))));
        if (empty($ids)) {
            return collect();
        }

        DB::beginTransaction();
        try {
            // Pull minimal relationships needed for decisions
            $conflicted = FacilityBooking::query()
                ->whereIn('FB_FacilityBookingID', $ids)
                ->with([

                    'facility',                                      // full Facility
                    'facility.facilityRestrictBookers:schedulingTeamId,schedulingTeamName',
                    'facilityBookingRecurrence',
                    'linkedToFacilityBooking',                    // parent if this is a linked booking
                    'linkedFacilityBookings.facility',
                    'createdBy:UD_UserID,UD_DisplayName,UD_InternalEmail',
                ])
                ->get();

            $declined = collect();
            foreach ($conflicted as $booking) {
                if ($booking->facility?->FC_DefaultBookingType !== Facility::DEFAULT_BOOKING_SELF_BOOKED) {
                    if ($this->isAdminForFacility($booking->createdBy, $booking->facility)) {
                        continue;
                    }
                }

                $date = $booking->FB_BookingStartDateTime->toDateString();

                if ($this->isPrimary($booking)) {
                    // PRIMARY → entire chain on this date, ignoring mandatory flags
                    $chain = $this->collectInstanceChainAll($booking, $date)
                        ->filter(fn(FacilityBooking $b) => !in_array($b->FB_BookingStatus, [
                            FacilityBooking::BOOKING_STATUS_CANCELLED,
                            FacilityBooking::BOOKING_STATUS_DECLINED,
                        ]))
                        ->filter(function (FacilityBooking $b) {
                            $isSelf = ($b->facility?->FC_DefaultBookingType === Facility::DEFAULT_BOOKING_SELF_BOOKED);
                            return $isSelf || !$this->isAdminForFacility($b->createdBy, $b->facility);
                        });

                    if ($chain->isNotEmpty()) {
                        $declined = $declined->merge($chain);
                    }
                } else {
                    // LINKED/SECONDARY
                    $isMandatory = $this->isMandatoryChildInstanceForDate($booking, $date);

                    if ($isMandatory) {
                        // Mandatory → decline entire chain (parent + all children on same date)
                        $chain = $this->collectInstanceChainAll($booking, $date)
                            ->filter(fn(FacilityBooking $b) => !in_array($b->FB_BookingStatus, [
                                FacilityBooking::BOOKING_STATUS_CANCELLED,
                                FacilityBooking::BOOKING_STATUS_DECLINED,
                            ]))
                            ->filter(function (FacilityBooking $b) {
                                $isSelf = ($b->facility?->FC_DefaultBookingType === Facility::DEFAULT_BOOKING_SELF_BOOKED);
                                return $isSelf || !$this->isAdminForFacility($b->createdBy, $b->facility);
                            });

                        if ($chain->isNotEmpty()) {
                            $declined = $declined->merge($chain);
                        }
                    } else {
                        // Non-mandatory → decline only this instance
                        if (
                            !in_array($booking->FB_BookingStatus, [
                            FacilityBooking::BOOKING_STATUS_CANCELLED,
                            FacilityBooking::BOOKING_STATUS_DECLINED,
                            ])
                        ) {
                            $declined->push($booking);
                        }
                    }
                }


                //  INVERSE PROPAGATION
                // Any upstream parents that mark THIS booking as MANDATORY on this date?
                $upstreamParents = $this->getMandatoryUpstreamParents($booking, $date);

                foreach ($upstreamParents as $parent) {
                    if ($parent->facility?->FC_DefaultBookingType !== Facility::DEFAULT_BOOKING_SELF_BOOKED) {
                        if ($this->isAdminForFacility($parent->createdBy, $parent->facility)) {
                            continue;
                        }
                    }

                    // Decline the parent's entire chain (parent + all linked on the same date)
                    $parentChain = $this->collectInstanceChainAll($parent, $date)
                        ->filter(fn(FacilityBooking $b) => !in_array($b->FB_BookingStatus, [
                            FacilityBooking::BOOKING_STATUS_CANCELLED,
                            FacilityBooking::BOOKING_STATUS_DECLINED,
                        ]));

                    if ($parentChain->isNotEmpty()) {
                        $declined = $declined->merge($parentChain);
                    }
                }
            }

            //  end inverse propagation
            $finalTargets = $declined
                ->filter(fn(\App\Models\FacilityBooking\FacilityBooking $b) => !in_array($b->FB_BookingStatus, [
                    \App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_CANCELLED,
                    \App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_DECLINED,
                ], true))
                ->unique('FB_FacilityBookingID')
                ->values();

            // ✅ Optional: ensure a decline reason is recorded (good for audit/email)
            $finalTargets->each(function (\App\Models\FacilityBooking\FacilityBooking $b) {
                if (empty($b->FB_BookingDeclineReason)) {
                    $b->FB_BookingDeclineReason = 'Declined due to facility unavailability';
                }
            });


            if ($finalTargets->isNotEmpty()) {
                $this->updateFacilityBookingStatusInstances(
                    facilityBookings: $finalTargets,
                    user: $actor,
                    status: FacilityBooking::BOOKING_STATUS_DECLINED,
                    history: 'Booking Declined due to Facility Unavailability'
                );
            }


            DB::commit();
            $finalTargets->each(function (\App\Models\FacilityBooking\FacilityBooking $b) {
                $b->loadMissing([
                    'facility',                        // child/primary facility for this booking
                    'facility.location',               // if `current_location` relies on `location`
                    'linkedToFacilityBooking.facility', // parent facility for linked bookings
                    'createdBy',                       // greeting and recipient email
                ]);
            });


            $groups = $finalTargets->groupBy(function (\App\Models\FacilityBooking\FacilityBooking $b) {
                return implode('|', [
                    optional($b->createdBy)->UD_InternalEmail,                    // recipient
                    $b->FB_FacilityBookingRecurrenceID,                           // recurrence
                ]);
            });

            foreach ($groups as $groupBookings) {
                $groupBookings->each(function (\App\Models\FacilityBooking\FacilityBooking $b) {
                    $b->loadMissing([
                        'facility',
                        'facility.location',
                        'linkedToFacilityBooking.facility',
                        'createdBy',
                        'facilityBookingRecurrence',
                    ]);
                });
                $primary = $groupBookings->first();

                $recipient = $primary->createdBy;
                if (empty($recipient?->UD_InternalEmail)) {
                    continue;
                }

                $cc = array_values(array_unique(
                    $groupBookings->flatMap(fn($booking) => $booking->facility->facility_contact_emails)
                        ->merge([$actor->UD_InternalEmail])
                        ->filter()
                        ->all()
                ));


                $declinedDates = $groupBookings
                    ->map(fn($b) => $b->FB_BookingStartDateTime->format('d/m/Y'))
                    ->unique()
                    ->values();


                $reason = optional($groupBookings->first(fn($b) => !empty($b->FB_BookingDeclineReason)))
                    ->FB_BookingDeclineReason ?? 'Declined due to facility unavailability';

                $ordered = $groupBookings->sortBy(fn($b) => $b->FB_BookingStartDateTime)->values();

                $recTitle = optional($primary->facilityBookingRecurrence)->FBR_BookingTitle;
                $subject  = $recTitle
                    ? "Facility bookings declined: {$recTitle} (recurrence summary)"
                    : "Facility bookings declined (recurrence summary)";

                Mail::to($recipient->UD_InternalEmail)
                    ->cc($cc)
                    ->send(new \App\Mail\FacilityBookingDeclinedDueToUnavailability(
                        user: $recipient,
                        facilityBooking: $primary,
                        declinedDates: $declinedDates,
                        reason: $reason,
                        bookings: $ordered
                    )->subject($subject));
            }

            return $finalTargets->unique(key: 'FB_FacilityBookingID')->values();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }



    private function isPrimary(FacilityBooking $booking): bool
    {
        return $booking->linkedToFacilityBooking === null;
    }


    private function isAdminForFacility(?User $user, ?Facility $facility): bool
    {
        if (!$user || !$facility) {
            return false;
        }

        return (bool) $user->haveAccessToFacility(facility: $facility);
    }



    private function isMandatoryChildInstanceForDate(FacilityBooking $child, string $date): bool
    {
        $parent = $child->linkedToFacilityBooking;
        if (!$parent) {
            return false;
        }

        return $parent->facilityBookingRecurrence
            ->facilityBookings()
            ->linkedBooking()
            ->where('FacilityBookings.FB_FacilityBookingID', $child->FB_FacilityBookingID)
            ->whereDate('FacilityBookings.FB_BookingStartDateTime', $date)
            ->where('FacilityLinks.FCLK_Mandatory', 1)
            ->exists();
    }


    private function collectInstanceChainAll(FacilityBooking $booking, string $date): Collection
    {
        if ($booking->linkedToFacilityBooking) {
            $parent = $booking->linkedToFacilityBooking;

            $siblings = $parent->facilityBookingRecurrence
                ->facilityBookings()
                ->linkedBooking()
                ->whereDate('FacilityBookings.FB_BookingStartDateTime', $date)
                ->with([
                    'facility:FC_FacilityID,FC_DefaultBookingType,FC_Contact,FC_AreaOwnerID',
                    'facility.facilityRestrictBookers:schedulingTeamId,schedulingTeamName',
                    'createdBy:UD_UserID,UD_DisplayName,UD_InternalEmail',
                ])

                ->get();

            return collect([$parent, $booking])
                ->merge($siblings)
                ->unique('FB_FacilityBookingID');
        }


        $children = $booking->facilityBookingRecurrence
            ->facilityBookings()
            ->linkedBooking()
            ->whereDate('FacilityBookings.FB_BookingStartDateTime', $date)
            ->with([
                'facility:FC_FacilityID,FC_DefaultBookingType,FC_Contact,FC_AreaOwnerID',
                'facility.facilityRestrictBookers:schedulingTeamId,schedulingTeamName',
                'createdBy:UD_UserID,UD_DisplayName,UD_InternalEmail',
            ])
            ->get();

        return collect([$booking])
            ->merge($children)
            ->unique('FB_FacilityBookingID');
    }



    private function getMandatoryUpstreamParents(FacilityBooking $booking, string $date): \Illuminate\Support\Collection
    {
        return FacilityBooking::query()
            // Parent instances occurring on the same calendar date
            ->whereDate('FacilityBookings.FB_BookingStartDateTime', $date)
            // Join to linked bookings + FacilityLinks using your existing local scope
            ->linkedBooking()
            // Limit to the case where the linked booking IS our conflicted booking
            ->where('linkedBookings.FB_FacilityBookingID', $booking->FB_FacilityBookingID)
            // Only mandatory links
            ->where('FacilityLinks.FCLK_Mandatory', 1)
            // Load what you need next
            ->with([
                'facility:FC_FacilityID,FC_DefaultBookingType,FC_Contact,FC_AreaOwnerID',
                'facility.facilityRestrictBookers:schedulingTeamId,schedulingTeamName',
                'facilityBookingRecurrence',
                'linkedFacilityBookings.facility',
                'createdBy:UD_UserID,UD_DisplayName,UD_InternalEmail'
            ])
            ->get();
    }


    public function cancelFacilityBookingData(FacilityBooking $facilityBooking, array $facilityBookingFormData, User $user)
    {
        $selectedValue = $facilityBookingFormData['cancel_value'];
        $notCancelled = [FacilityBooking::BOOKING_STATUS_CANCELLED, FacilityBooking::BOOKING_STATUS_DECLINED];

        $targets = collect();

        if ($selectedValue === "some_booking") {
            $ids = collect($facilityBookingFormData['some_booking_list'] ?? [])
                ->filter()
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values();

            if ($ids->isEmpty()) {
                return $facilityBooking; // nothing to do
            }

            // Load the selected bookings to determine parent vs child
            $selectedBookings = $facilityBooking->facilityBookingRecurrence
                ->facilityBookings()
                ->whereIn('FB_FacilityBookingID', $ids->all())
                ->whereNotIn('FB_BookingStatus', $notCancelled)
                ->with([
                    'facility',
                    'linkedToFacilityBooking', // if not null => this is a CHILD
                ])
                ->get();

            foreach ($selectedBookings as $selected) {
                $date = $selected->FB_BookingStartDateTime->toDateString();

                if ($selected->linkedToFacilityBooking) {
                    // ✅ Selected item is a CHILD → cancel ONLY this child
                    $targets->push($selected);
                    continue;
                }

                $childIds = $selected->facilityBookingRecurrence
                    ->facilityBookings()
                    ->linkedBooking()
                    // Scope to the parent instance:
                    ->where('FacilityBookings.FB_FacilityBookingID', $selected->FB_FacilityBookingID)
                    ->whereDate('FacilityBookings.FB_BookingStartDateTime', $date)
                    ->select([
                        DB::raw('linkedBookings.FB_FacilityBookingID AS child_id'),
                    ])
                    ->pluck('child_id');

                // 2) Load actual child models (so the status update + email pipeline works)
                $mandatoryChildren = FacilityBooking::query()
                    ->whereIn('FB_FacilityBookingID', $childIds->all())
                    ->whereNotIn('FB_BookingStatus', $notCancelled)
                    ->with('facility')
                    ->get();

                // Parent + its mandatory children
                $targets->push($selected);
                $targets = $targets->merge($mandatoryChildren);
            }
        } elseif ($selectedValue === "entire_booking") {
            $parents = $facilityBooking->facilityBookingRecurrence
                ->facilityBookings()
                ->whereDate('FB_BookingStartDateTime', '>=', Carbon::now()->startOfDay())
                ->whereNotIn('FB_BookingStatus', $notCancelled)
                ->with('facility')
                ->get();

            foreach ($parents as $parent) {
                $date = $parent->FB_BookingStartDateTime->toDateString();

                $children = $parent->facilityBookingRecurrence
                    ->facilityBookings()
                    ->linkedBooking()
                    ->whereDate('FacilityBookings.FB_BookingStartDateTime', $date)
                    ->where('FacilityLinks.FCLK_Mandatory', 1)
                    ->whereNotIn('FacilityBookings.FB_BookingStatus', $notCancelled)
                    ->with('facility')
                    ->get();

                $targets->push($parent);
                $targets = $targets->merge($children);
            }
        } elseif ($selectedValue === "recurrence_booking") {
            $from = Carbon::parse($facilityBookingFormData['from_date'])->startOfDay();
            $to   = Carbon::parse($facilityBookingFormData['to_date'])->endOfDay()->subSecond(1); //Database considering next day so reducing precision time by 1 second

            $parents = $facilityBooking->facilityBookingRecurrence
                ->facilityBookings()
                ->whereBetween('FB_BookingStartDateTime', [$from, $to])
                ->whereNotIn('FB_BookingStatus', $notCancelled)
                ->with('facility')
                ->get();

            foreach ($parents as $parent) {
                $date = $parent->FB_BookingStartDateTime->toDateString();

                $children = $parent->facilityBookingRecurrence
                    ->facilityBookings()
                    ->linkedBooking()
                    ->whereDate('FacilityBookings.FB_BookingStartDateTime', $date)
                    ->where('FacilityLinks.FCLK_Mandatory', 1)
                    ->whereNotIn('FacilityBookings.FB_BookingStatus', $notCancelled)
                    ->with('facility')
                    ->get();

                $targets->push($parent);
                $targets = $targets->merge($children);
            }
        } elseif ($selectedValue === "nonmandatory_booking") {
            // Existing behaviour (cancel non-mandatory linked children across future)
            $targets = $facilityBooking->facilityBookingRecurrence
                ->facilityBookings()
                ->linkedBooking()
                ->where('FacilityLinks.FCLK_Mandatory', 0)
                ->whereDate('FacilityBookings.FB_BookingStartDateTime', '>=', Carbon::now()->startOfDay())
                ->whereNotIn('FacilityBookings.FB_BookingStatus', $notCancelled)
                ->with('facility')
                ->get();
        } else {
            // Single booking fallback (legacy)
            $bookingId = (int) ($facilityBookingFormData['bookingId'] ?? 0);
            $targets = $facilityBooking->facilityBookingRecurrence
                ->facilityBookings()
                ->where('FB_FacilityBookingID', '=', $bookingId)
                ->whereNotIn('FB_BookingStatus', $notCancelled)
                ->with('facility')
                ->orderBy('FB_BookingEndDateTime', 'desc')
                ->take(1)
                ->get();
        }

        $targets = $targets->filter()->unique('FB_FacilityBookingID')->values();
        if ($targets->isEmpty()) {
            return $facilityBooking;
        }

        // Reuse your existing updater (status + history + ONE email with all dates)
        $this->updateFacilityBookingStatusInstances(
            facilityBookings: $targets,
            user: $user,
            status: FacilityBooking::BOOKING_STATUS_CANCELLED,
            history: 'Booking Cancelled'
        );

        // Keep return shape as before
        return $targets->first();
    }


    private function updateFacilityBookingStatusInstances(Collection $facilityBookings, User $user, string $status, string $history): void
    {
        $cancelledDates = collect([]);
        foreach ($facilityBookings as $facilityBookingM) {
            $facilityBookingM->FB_BookingPreviousStatus = $facilityBookingM->FB_BookingStatus;
            $facilityBookingM->FB_BookingStatus = $status;

            if ($status === FacilityBooking::BOOKING_STATUS_CANCELLED) {
                $facilityBookingM->FB_BookingCancelDate = Carbon::now();
            } elseif ($status === FacilityBooking::BOOKING_STATUS_DECLINED) {
                $facilityBookingM->FB_BookingDeclineDate = Carbon::now();
            }

            $facilityBookingM->FB_UpdatedBy = $user->UD_UserID;
            $facilityBookingM->save();
            if ($status === FacilityBooking::BOOKING_STATUS_CANCELLED) {
                $cancelledDates->push($facilityBookingM->FB_BookingStartDateTime->format('d/m/Y'));
            }
            //Save history
            $historyData = [];
            $historyData[] = __(':history by :user on :datetime', [
                'history' => $history,
                'user' => $user->UD_DisplayName,
                'datetime' => HistoryLog::DATETIME_REPLACE_STRING
            ]);
            $this->storeFacilityBookingHistory($facilityBookingM, $historyData, $user);
        }
        if (isset($facilityBookingM)) {
            if (isset($facilityBookingM) && $facilityBookingM->createdBy != null && $status == FacilityBooking::BOOKING_STATUS_CANCELLED) {
                Mail::to($facilityBookingM->createdBy->UD_InternalEmail)
                    ->cc(array_merge([$user->UD_InternalEmail], $facilityBookingM->facility->facility_contact_emails))
                    ->send(new FacilityBookingCancelled($facilityBookingM->createdBy, $facilityBookingM, $cancelledDates));
            }
        }
    }



    public function cancelFacilityBooking(FacilityBooking $facilityBooking, array $facilityBookingFormData, User $user)
    {
        $selectedValue = $facilityBookingFormData['cancel_value'];

        // Common filters
        $today = Carbon::now()->startOfDay();
        $notActiveStatuses = [
            FacilityBooking::BOOKING_STATUS_CANCELLED,
            FacilityBooking::BOOKING_STATUS_DECLINED,
        ];

        if ($selectedValue === "some_booking") {
            // 1) Parents (future, active)
            $parents = $facilityBooking
                ->facilityBookingRecurrence
                ->facilityBookings()
                ->where('FB_FacilityID', $facilityBooking->facility->FC_FacilityID)
                ->whereDate('FB_BookingStartDateTime', '>=', $today)
                ->whereNotIn('FB_BookingStatus', $notActiveStatuses)
                ->orderBy('FB_BookingStartDateTime', 'asc')
                ->select([
                    'FB_FacilityBookingID',
                    'FB_BookingTitle',
                    'FB_BookingStartDateTime',
                    'FB_BookingEndDateTime',
                ])
                ->get();

            if ($parents->isEmpty()) {
                return [];
            }

            // 2) Build one span for bulk children fetch
            $parentDates = $parents->map(fn($p) => $p->FB_BookingStartDateTime->toDateString())->unique();
            $minDate = Carbon::parse($parentDates->min())->startOfDay();
            $maxDate = Carbon::parse($parentDates->max())->endOfDay();

            // 3) Non‑mandatory children (SELECT FROM linkedBookings alias!)
            $children = $facilityBooking->facilityBookingRecurrence
                ->facilityBookings()
                ->linkedBooking()
                ->whereBetween('FacilityBookings.FB_BookingStartDateTime', [$minDate, $maxDate])
                ->where('FacilityLinks.FCLK_Mandatory', 0)
                ->whereNotIn('FacilityBookings.FB_BookingStatus', $notActiveStatuses)
                ->whereNotIn('linkedBookings.FB_BookingStatus', $notActiveStatuses)
                ->select([
                    // Map linked child columns back to the base attribute names:
                    DB::raw('linkedBookings.FB_FacilityBookingID      AS FB_FacilityBookingID'),
                    DB::raw('linkedBookings.FB_BookingTitle            AS FB_BookingTitle'),
                    DB::raw('linkedBookings.FB_BookingStartDateTime    AS FB_BookingStartDateTime'),
                    DB::raw('linkedBookings.FB_BookingEndDateTime      AS FB_BookingEndDateTime'),
                ])
                ->orderBy('linkedBookings.FB_BookingStartDateTime', 'asc')
                ->get();

            // 4) Group children by calendar day
            $childrenByDate = $children->groupBy(function ($b) {
                return $b->FB_BookingStartDateTime->toDateString();
            });

            // 5) Build grouped payload (parent + non‑mandatory children only)
            $groups = [];
            foreach ($parents as $parent) {
                $date = $parent->FB_BookingStartDateTime->toDateString();
                $nonMandatory = $childrenByDate->get($date, collect());

                $groups[] = [
                    'date'   => $date,
                    'parent' => [
                        'booking_id'    => $parent->FB_FacilityBookingID,
                        'booking_title' => $parent->FB_BookingTitle,
                        'start_date'    => $parent->FB_BookingStartDateTime->format('d-m-Y H:i:s'),
                        'end_date'      => $parent->FB_BookingEndDateTime->format('d-m-Y H:i:s'),
                    ],
                    'non_mandatory_children' => $nonMandatory->map(function ($b) {
                        return [
                            'booking_id'    => $b->FB_FacilityBookingID, // <-- now child ID is correct
                            'booking_title' => $b->FB_BookingTitle,
                            'start_date'    => $b->FB_BookingStartDateTime->format('d-m-Y H:i:s'),
                            'end_date'      => $b->FB_BookingEndDateTime->format('d-m-Y H:i:s'),
                            'mandatory'     => 0,
                            'is_linked'     => 1,
                        ];
                    })->values()->all(),
                ];
            }

            return $groups;
        }



        // RANGE / ENTIRE (payload for datepicker bounds)
        // We exclude cancelled/declined here as well.
        $baseQuery = $facilityBooking
            ->facilityBookingRecurrence
            ->facilityBookings()
            ->where('FB_FacilityID', $facilityBooking->facility->FC_FacilityID)
            ->whereDate('FB_BookingStartDateTime', '>=', $today)
            ->whereNotIn('FB_BookingStatus', $notActiveStatuses);

        // Earliest future START for this series (if any), else today
        $earliestStart = (clone $baseQuery)->min('FB_BookingStartDateTime');
        $furthestEnd   = (clone $baseQuery)->max('FB_BookingEndDateTime');

        $minDate = $earliestStart
            ? Carbon::parse($earliestStart)->toDateString()
            : $today->toDateString();

        $maxDate = $furthestEnd
            ? Carbon::parse($furthestEnd)->toDateString()
            : $today->toDateString();

        return [
            'minDate' => $minDate, // ISO for your datepicker init
            'maxDate' => $maxDate,
        ];
    }

    /**
     * Store Facility Booking history
     *
     * @param FacilityBooking $facilityBooking
     * @param array $facilityHistoryLog
     * @param User $user
     */
    public function storeFacilityBookingHistory(FacilityBooking $facilityBooking, array $facilityBookingHistoryLog, User $user): ?HistoryLog
    {
        if (empty($facilityBookingHistoryLog)) {
            return null;
        }
        $historyLog = new HistoryLog();
        $historyLog->HL_HLogs = $facilityBookingHistoryLog;
        $historyLog->HL_Created_BY = $user->UD_UserID;
        $historyLog->HL_Type = HistoryLog::FACILITY_BOOKING;
        $facilityBooking->history()->save($historyLog);
        return $historyLog;
    }

    /**
     * Store Facility scheduler note history
     *
     * @param FacilityBookerNote $facilityBookerNote
     * @param array $facilityBookerNoteHistoryLog
     * @param User $user
     */
    private function storeFacilityBookerNoteHistory(FacilityBookerNote $facilityBookerNote, array $facilityBookerNoteHistoryLog, User $user): ?HistoryLog
    {
        if (empty($facilityBookerNoteHistoryLog)) {
            return null;
        }
        $historyLog = new HistoryLog();
        $historyLog->HL_HLogs = $facilityBookerNoteHistoryLog;
        $historyLog->HL_Created_BY = $user->UD_UserID;
        $historyLog->HL_Type = HistoryLog::FACILITY_BOOKER_NOTE;
        $facilityBookerNote->history()->save($historyLog);
        return $historyLog;
    }

    /**
     * Delete Facility Booking
     *
     * @param FacilityBooking $facilityBooking
     * @param User $user
     */
    public function destroyFacilityBooking(FacilityBooking $facilityBooking, User $user): bool
    {
        $facilityBookings =  $facilityBooking->facilityBookingRecurrence->facilityBookings()->with('facility')->get();

        foreach ($facilityBookings as $facilityBookingM) {
            //Save history
            $historyData[] = __('Booking deleted by :user on :datetime', [
                'user' => $user->UD_DisplayName,
                'datetime' => HistoryLog::DATETIME_REPLACE_STRING
            ]);
            $this->storeFacilityBookingHistory($facilityBookingM, $historyData, $user);

            $facilityBookingM->delete();
        }

        return true;
    }

    /**
     * Delete scheduler notes
     *
     * @param FacilityBookerNote $facilityBookerNote Facility booker note
     * @param User $user user performing action
     *
     */
    public function deleteSchedulerNote(FacilityBookerNote $facilityBookerNote, User $user): void
    {
        foreach ($facilityBookerNote->facilityBookerNoteRecurrence->facilityBookerNotes as $facilityBookerNoteM) {
            //Save history
            $historyData[] = __('Scheduler Note deleted by :user on :datetime', [
                'user' => $user->UD_DisplayName,
                'datetime' => HistoryLog::DATETIME_REPLACE_STRING
            ]);
            $this->storeFacilityBookerNoteHistory($facilityBookerNoteM, $historyData, $user);
            $facilityBookerNoteM->delete();
        }
    }
}
