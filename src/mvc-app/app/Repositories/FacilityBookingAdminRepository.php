<?php

namespace App\Repositories;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBooking;
use App\Models\User;
use App\Repositories\Contracts\FacilityBookingAdminRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FacilityBookingAdminRepository implements FacilityBookingAdminRepositoryInterface
{
    /**
     * Gets the list of facility details and its bookings
     * @param User $user user
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */

    public function getAdministratorFacilityBookings(User $user, Carbon $startDate, Carbon $endDate, string $status, string $list_type): array
    {
        $returnData = [];

        $queryData = $this->facilityBookingQuery();

        if ($status == FacilityBooking::BOOKING_STATUS_PENDING && $list_type == "Short Notice") {
            $queryData->whereBetween('FB_BookingStartDateTime', [$startDate->startOfDay(), $endDate->endOfDay()])->whereIn('FB_BookingStatus', [$status, FacilityBooking::BOOKING_STATUS_NEW]);
        } elseif ($status == FacilityBooking::BOOKING_STATUS_NEW && $list_type == "New_Previous") {
            $queryData->where('FB_BookingStartDateTime', '<', $startDate)->where('FB_BookingStatus', '=', $status);
        } elseif ($status == FacilityBooking::BOOKING_STATUS_NEW && $list_type == "New_Future") {
            $queryData->where('FB_BookingStartDateTime', '>', $endDate)->where('FB_BookingStatus', '=', $status);
        } elseif ($status == FacilityBooking::BOOKING_STATUS_PENDING && $list_type == "Pending_Previous") {
            $queryData->where('FB_BookingStartDateTime', '<', $startDate)->where('FB_BookingStatus', '=', $status);
        } elseif ($status == FacilityBooking::BOOKING_STATUS_PENDING && $list_type == "Pending_Future") {
            $queryData->where('FB_BookingStartDateTime', '>', $endDate)->where('FB_BookingStatus', '=', $status);
        } elseif ($status == FacilityBooking::BOOKING_STATUS_DECLINED && $list_type == "Declined") {
            $queryData->where('FB_BookingStartDateTime', '>=', $startDate->startOfDay())->where('FB_BookingStatus', '=', $status);
        } elseif ($status == FacilityBooking::BOOKING_STATUS_CANCELLED && $list_type == "Cancelled") {
            $queryData->where('FB_BookingStartDateTime', '>=', $startDate->startOfDay())->where('FB_BookingStatus', '=', $status);
        }

        foreach ($queryData->get() as $key => $facilityBookingData) {
            $check_user_access = $user->haveAccessToFacility($facilityBookingData->facility);

            if ($check_user_access == true) {
                $returnData[$key]['recur'] = "No";
                if ($facilityBookingData->facilityBookingRecurrence->is_recurring) {
                    $returnData[$key]['recur'] = "Yes";
                }
                $returnData[$key]['linked_facility'] = '';
                $linkedFacilityBookings = collect([$facilityBookingData->FB_BookingTitle]);
                $linkedFacilities = collect();
                if ($facilityBookingData->linkedToFacilityBooking && $facilityBookingData->linkedToFacilityBooking->facility) {
                    $linkedFacilities->push($facilityBookingData->linkedToFacilityBooking->facility->FC_FacilityName);
                }
                foreach ($facilityBookingData->linkedFacilityBookings as $linkedFacilityBooking) {
                    if ($linkedFacilityBooking->facility) {
                        $linkedFacilities->push($linkedFacilityBooking->facility->FC_FacilityName);
                    }
                }
                $returnData[$key]['linked_facility'] = $linkedFacilities->filter()->unique()->values()->toArray();

                if ($facilityBookingData->linkedToFacilityBooking) {
                    $linkedFacilityBookings->push($facilityBookingData->linkedToFacilityBooking->FB_BookingTitle);
                }

                if ($facilityBookingData->linkedToFacilityBooking) {
                    foreach ($facilityBookingData->linkedToFacilityBooking->linkedFacilityBookings as $linkedFacilityBooking) {
                        $linkedFacilityBookings->push($linkedFacilityBooking->FB_BookingTitle);
                    }
                }
                $returnData[$key]['linked_bookings'] = $linkedFacilityBookings->unique()->toArray();

                $returnData[$key]['external_customer'] = '';
                if ($facilityBookingData->externalCustomer != null) {
                    $returnData[$key]['external_customer'] = $facilityBookingData->externalCustomer->EC_CompanyName;
                }

                $returnData[$key]['FST_FacilitySubType'] = '';
                if ($facilityBookingData->FB_FacilitySubTypeID != null) {
                    $returnData[$key]['FST_FacilitySubType'] = $facilityBookingData->facilitySubTypes->FST_FacilitySubType;
                }
                $returnData[$key]['location'] = '';
                if ($facilityBookingData->facility->current_location != null) {
                    $returnData[$key]['location'] = $facilityBookingData->facility->current_location;
                }
                $returnData[$key]['facility_bookings'] = $facilityBookingData->toArray();
                $returnData[$key]['booking_type'] = $facilityBookingData->facility->FC_DefaultBookingType ?? '';
            }
        }
        return $returnData;
    }

    protected function facilityBookingQuery()
    {
        return FacilityBooking::with(['facility.location', 'facility.internalLocation', 'facilityBookingRecurrence', 'facilitySubTypes', 'externalCustomer', 'linkedToFacilityBooking.linkedFacilityBookings.facility', 'linkedFacilityBookings.facility', 'linkedToFacilityBooking.facility']);
    }
}
