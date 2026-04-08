<?php

namespace App\Repositories;

use App\Mail\Facility\FacilityAdministratorUpdatedMail;
use App\Models\Facility\Equipment;
use App\Models\Facility\Facility;
use App\Models\Facility\FacilityAvailability;
use App\Models\Facility\FacilityChangeBookingType;
use App\Models\Facility\FacilityLocation;
use App\Models\Facility\FacilityMarkUnavailable;
use App\Models\FacilityBooking\FacilityBooking;
use App\Models\Facility\FacilitySubType;
use App\Models\Facility\FacilityType;
use App\Models\Facility\Service;
use App\Models\HistoryLog;
use App\Models\Scheduling\Division;
use App\Models\Scheduling\SchedulingTeam;
use App\Models\User;
use App\Repositories\Contracts\FacilityBookingRepositoryInterface;
use App\Repositories\Contracts\FacilityRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class FacilityRepository implements FacilityRepositoryInterface
{
    /**
     * Returns facility list
     *
     * @return Collection
     */
    public function getFacilityList(): Collection
    {
        return Facility::with([
            'facilityAreaOwner:DivisionID,DivisionName',
            'location',
            'internalLocation:LN_LocationID,LN_Location',
            'facilityType:FT_FacilityTypeID,FT_FacilityType',
            'facilitySubTypes:FST_FacilitySubTypeID,FST_FacilitySubType',
            'facilityServices:SR_ServiceID,SR_Service',
            'facilityEquipments:EQ_EquipmentID,EQ_Equipment',
            'facilityAvailability',
            'facilityMarkAsUnavailable',
            'facilityRestrictBookers',
            'facilityAreaOwner.schedulingTeam',
            'linkedFacilities'
        ])->get();
    }

    /**
     * Returns area list
     * @param User $user user
     * @return Collection
     */
    public function getAreaList(?User $user = null): Collection
    {
        $areaList = Division::active()->get();
        if ($user != null) { // Filter area where user is facility admin
            $areaList = $areaList->filter(function ($value) use ($user) {
                return $user->getAreasRoles->where('RoleName', 'Facility Administrator')
                    ->where('DivisionID', $value->DivisionID)->count() > 0;
            });
        }
        return $areaList;
    }

    /**
     * Returns team associated to the are list
     *
     * @return Collection
     */
    public function getAreaTeamList(Division $area): Collection
    {
        return $area->schedulingTeam;
    }

    /**
     * Save facility
     *
     * @param User $user user
     * @param array $facilityData facility data
     * @param Facility $editFacility Facility to be updated
     *
     * @return Facility
     */
    public function saveFacility(User $user, array $facilityData, ?Facility $editFacility = null): Facility
    {
        $facilityMainData = $facilityData['facilityFormData'];
        $facilityFacilityType = $facilityData['facilityTypeForm'];
        $historyData = [];
        DB::beginTransaction();
        try {
            $history = [];
            //Save facility
            $facility = $editFacility ?? new Facility();
            $facility->FC_FacilityName = $facilityMainData['facility_frm'];
            if ($editFacility != null && $facility->isDirty('FC_FacilityName')) { //History for FacilityName
                $history[] = __('Facility name changed from :data1 to :data2', ['data1' => ($facility->getOriginal('FC_FacilityName')), 'data2' => ($facility->FC_FacilityName)]);
            }

            $facility->FC_ActiveFrom = Carbon::createFromFormat('d/m/Y', $facilityMainData['active_from_frm'])->format('Y-m-d');

            if ($editFacility != null && $facility->isDirty('FC_ActiveFrom')) { //History active from
                $history[] = __('Active from date changed from :date1 to :date2', ['date1' => Carbon::parse($facility->getOriginal('FC_ActiveFrom'))->format('d/m/Y'), 'date2' => Carbon::parse($facility->FC_ActiveFrom)->format('d/m/Y')]);
            }

            $facility->FC_AreaOwnerID = $facilityMainData['area_owner_frm'];

            if ($editFacility != null && $facility->isDirty('FC_AreaOwnerID')) { //History area owner
                $history[] = __('Area Owner changed from :area1 to :area2', ['area1' => Division::find($facility->getOriginal('FC_AreaOwnerID'))->DivisionName, 'area2' => Division::find($facility->FC_AreaOwnerID)->DivisionName]);
            }

            //Facility Provider type based which address type is mapped
            $existingLocation = $editFacility != null ? $facility->current_location : '';
            $facility->FC_ProviderType = $facilityMainData['provider_type_frm'];

            if ($editFacility != null && $facility->isDirty('FC_ProviderType')) { //History provider type
                $history[] = __('Provider Type changed from :type1 to :type2', ['type1' => FacilityLocation::providerTypeList()[$facility->getOriginal('FC_ProviderType')], 'type2' => FacilityLocation::providerTypeList()[$facility->FC_ProviderType]]);
            }

            $facility->FC_ProviderName = $facilityMainData['provider_name_frm'];

            if ($editFacility != null && $facility->isDirty('FC_ProviderName')) { //History provider name
                $history[] = __('Provider Name changed from :name1 to :name2', ['name1' => $facility->getOriginal('FC_ProviderName'), 'name2' => $facility->FC_ProviderName]);
            }

            $facility->FC_LocationNote = $facilityMainData['location_notes_frm'];

            if ($editFacility != null && $facility->isDirty('FC_LocationNote')) { //History location note
                $history[] = __('Location Note changed from :location1 to :location2', ['location1' => $facility->getOriginal('FC_LocationNote'), 'location2' => $facility->FC_LocationNote]);
            }

            $facility->FC_FacilityCapacity = $facilityMainData['facility_capacity_frm'];

            if ($editFacility != null && $facility->isDirty('FC_FacilityCapacity')) { //History capacity
                $history[] = __('Capacity changed from :capacity1 to :capacity2', ['capacity1' => $facility->getOriginal('FC_FacilityCapacity'), 'capacity2' => $facility->FC_FacilityCapacity]);
            }

            $facility->FC_Accessible = $facilityMainData['facility_accessible_frm'];

            if ($editFacility != null && $facility->isDirty('FC_Accessible')) { //History accessible
                $history[] = __('Accessible changed from :accessible1 to :accessible2', ['accessible1' => Facility::accessibleList()[$facility->getOriginal('FC_Accessible')], 'accessible2' => Facility::accessibleList()[$facility->FC_Accessible]]);
            }

            $facility->FC_AccessibilityNote = $facilityMainData['accessibility_notes_frm'] ?? '';

            if ($editFacility != null && $facility->isDirty('FC_AccessibilityNote')) { //History accessible note
                $history[] = __('Accessible note changed from :accessible1 to :accessible2', ['accessible1' => $facility->getOriginal('FC_AccessibilityNote'), 'accessible2' => $facility->FC_AccessibilityNote]);
            }

            $facility->FC_DefaultBookingType = $facilityMainData['facility_default_booking_frm'];

            if ($facilityMainData['facility_default_booking_frm'] === Facility::DEFAULT_BOOKING_SELF_BOOKED && $editFacility != null && $facility->isDirty('FC_DefaultBookingType')) {
                $cancelOrConfirm = isset($facilityMainData['booking_status_declined_or_confirm']) &&
                    $facilityMainData['booking_status_declined_or_confirm'] == 'confirmed'
                    ? FacilityBooking::BOOKING_STATUS_CONFIRMED
                    : FacilityBooking::BOOKING_STATUS_DECLINED;

                $fromBookingStatusDay = (int) ($facilityMainData['allow_self_booking_from_frm'] ?? 0);
                $toBookingStatusDay   = (int) ($facilityMainData['allow_self_booking_to_frm'] ?? 365);

                $startBookingDate = now()->addDays($fromBookingStatusDay);
                $endBookingDate   = now()->addDays($toBookingStatusDay)->endOfDay();
                $conflictAllIds = request()->input('_conflict_ids', []);

                $updatedCount = $this->updateFacilityBookings($facility, $cancelOrConfirm, $startBookingDate, $endBookingDate, $conflictAllIds, $user);

                $linkedFacilities = $facility->linkedFacilities()->get();

                foreach ($linkedFacilities as $linkedFacility) {
                    $this->updateFacilityBookings($linkedFacility, $cancelOrConfirm, $startBookingDate, $endBookingDate, $conflictAllIds, $user);
                }

                if ($updatedCount > 0 || $linkedFacilities->isNotEmpty()) {
                    $history[] = __(
                        'Booking statuses changed to :newStatus for bookings between :from and :to',
                        [
                            'newStatus' => $cancelOrConfirm,
                            'from' => $startBookingDate->toDateString(),
                            'to'   => $endBookingDate->toDateString(),
                        ]
                    );
                }
            }

            if ($editFacility != null && $facility->isDirty('FC_DefaultBookingType')) { //History deafult booking type
                $history[] = __('Default Booking Type changed from :dbt1 to :dbt2', ['dbt1' => Facility::defaultBookingList()[$facility->getOriginal('FC_DefaultBookingType')], 'dbt2' => Facility::defaultBookingList()[$facility->FC_DefaultBookingType]]);
            }

            $facility->FC_MakeAllBookingsPrivate = $facilityMainData['facility_make_bookings_private_frm'];

            if ($editFacility != null && $facility->isDirty('FC_MakeAllBookingsPrivate')) { //History Make all booking private
                $now = Carbon::now();

                if ($facilityMainData['facility_make_bookings_private_frm'] === Facility::BOOKING_PRIVATE_YES) {
                    $facility->facilityBookings()
                        ->where('FB_BookingStartDateTime', '>', $now)
                        ->update([
                            'FB_Private' => Facility::BOOKING_PRIVATE_YES
                        ]);
                } elseif ($facilityMainData['facility_make_bookings_private_frm'] === Facility::BOOKING_PRIVATE_SUMMARY) {
                    $facility->facilityBookings()
                        ->where('FB_BookingStartDateTime', '>', $now)
                        ->where('FB_Private', '!=', Facility::BOOKING_PRIVATE_YES)
                        ->update([
                            'FB_Private' => Facility::BOOKING_PRIVATE_SUMMARY
                        ]);
                }
                $history[] = __('Make All Bookings Private changed from :mabp1 to :mabp2', ['mabp1' => Facility::bookingPrivateList()[$facility->getOriginal('FC_MakeAllBookingsPrivate')], 'mabp2' => Facility::bookingPrivateList()[$facility->FC_MakeAllBookingsPrivate]]);
            }

            $facility->FC_AllowBookingRequest = isset($facilityMainData['allow_booking_frm']) && $facilityMainData['allow_booking_frm'] == 'yes' ? 1 : 0;

            if ($editFacility != null && $facility->isDirty('FC_AllowBookingRequest')) { //History Allow Booking Request
                $history[] = __('Allow Booking Request changed from :abr1 to :abr2', ['abr1' => $facility->getOriginal('FC_MakeAllBookingsPrivate') == 1 ? 'Yes' : 'No', 'abr2' => $facility->FC_MakeAllBookingsPrivate == 1 ? 'Yes' : 'No']);
            }

            $facility->FC_OneOffBookingsOnly = isset($facilityMainData['on_off_booking_frm']) && $facilityMainData['on_off_booking_frm'] == 'yes' ? 1 : 0;

            if ($editFacility != null && $facility->isDirty('FC_OneOffBookingsOnly')) { //History One-Off Bookings Only
                $history[] = __('One-Off Bookings Only Request changed from :obr1 to :obr2', ['obr1' => $facility->getOriginal('FC_OneOffBookingsOnly') == 1 ? 'Yes' : 'No', 'obr2' => $facility->FC_OneOffBookingsOnly == 1 ? 'Yes' : 'No']);
            }

            $facility->FC_AllowSelfBookingFrom = $facilityMainData['allow_self_booking_from_frm'] ?? 0;

            if ($editFacility != null && $facility->isDirty('FC_AllowSelfBookingFrom')) { //History Allow Self Booking From
                $history[] = __('Allow Self Booking From changed from :asb1 to :asb2', ['asb1' => $facility->getOriginal('FC_AllowSelfBookingFrom'), 'asb2' => $facility->FC_AllowSelfBookingFrom]);
            }

            $facility->FC_AllowSelfBookingTo = $facilityMainData['allow_self_booking_to_frm'] ?? 365;

            if ($editFacility != null && $facility->isDirty('FC_AllowSelfBookingTo')) { //History Allow Self Booking To
                $history[] = __('Allow Self Booking To changed from :asb1 to :asb2', ['asb1' => $facility->getOriginal('FC_AllowSelfBookingTo'), 'asb2' => $facility->FC_AllowSelfBookingTo]);
            }

            $facility->FC_PopupNote = $facilityMainData['pop_up_notes_frm'];

            if ($editFacility != null && $facility->isDirty('FC_PopupNote')) { //History Popup notes
                $history[] = __('Pop-up Notes changed from :pn1 to :pn2', ['pn1' => $facility->getOriginal('FC_PopupNote'), 'pn2' => $facility->FC_PopupNote]);
            }

            $facility->FC_FacilityNote = $facilityMainData['facility_notes_frm'];

            if ($editFacility != null && $facility->isDirty('FC_FacilityNote')) { //History Facility notes
                $history[] = __('Facility Notes changed from :fn1 to :fn2', ['fn1' => $facility->getOriginal('FC_FacilityNote'), 'fn2' => $facility->FC_FacilityNote]);
            }

            $facility->FC_FacilityTypeID = $facilityFacilityType['facility_type_frm'];

            if ($editFacility != null && $facility->isDirty('FC_FacilityTypeID')) { //History Facility type
                $history[] = __('Facility Type changed from :ft1 to :ft2', ['ft1' => FacilityType::find($facility->getOriginal('FC_FacilityTypeID'))->FT_FacilityType, 'ft2' => FacilityType::find($facility->FC_FacilityTypeID)->FT_FacilityType]);
            }

            $facility->FC_Contact = $facilityMainData['facility_contact'];
            //History contact
            if ($editFacility !== null) {
                $oldEmails = $this->normalizeEmails($facility->getOriginal('FC_Contact'));
                $newEmails = $this->normalizeEmails($facility->FC_Contact);
                if ($oldEmails !== $newEmails) {
                    $history[] = __('Contact changed from :date1 to :date2', ['date1' => implode(', ', $oldEmails), 'date2' => implode(', ', $newEmails)]);
                }
            }

            $facility->primary_facility = isset($facilityMainData['primary_facility_frm']) && $facilityMainData['primary_facility_frm'] == 'yes' ? 1 : 0;

            if ($editFacility != null && $facility->isDirty('primary_facility')) { //History Primary Facility
                $history[] = __('Primary Facility changed from :pf1 to :pf2', ['pf1' => $facility->getOriginal('primary_facility') == 1 ? 'Yes' : 'No', 'pf2' => $facility->primary_facility == 1 ? 'Yes' : 'No']);
            }

            if (!empty($history)) {
                $historyData[] = implode(', ', $history) . __(' by :user on :datetime', ['user' => $user->UD_DisplayName, 'datetime' => HistoryLog::DATETIME_REPLACE_STRING]);
            }

            if ($editFacility == null) {
                $facility->FC_CreatedBy = $user->UD_UserID;
            } else {
                $facility->FC_CreatedDate = $facility->FC_CreatedDate;
            }
            $facility->FC_UpdatedBy = $user->UD_UserID;
            $facility->save();

            //Facility restrict bookers
            if ($editFacility != null) { // History of restrict bookers
                $existingRestrictBookers = $facility->facilityRestrictBookers;
                $newRestrictBookers = SchedulingTeam::whereIn('schedulingTeamId', $facilityMainData['restrict_bookers_frm'] ?? [0])->get();
                if ($existingRestrictBookers->diff($newRestrictBookers)->count() > 0 || $newRestrictBookers->diff($existingRestrictBookers)->count() > 0) {
                    if (empty($facilityMainData['restrict_bookers_frm'] ?? [])) {
                        $historyData[] = __('Restrict Bookers removed :data1 by :user on :datetime', ['data1' => $existingRestrictBookers->pluck('schedulingTeamName')->implode(', '), 'user' => $user->UD_DisplayName, 'datetime' => HistoryLog::DATETIME_REPLACE_STRING]);
                    } elseif ($existingRestrictBookers->count() == 0) {
                        $historyData[] = __('Restrict Bookers updated as :data2 by :user on :datetime', ['data2' => $newRestrictBookers->pluck('schedulingTeamName')->implode(', '), 'user' => $user->UD_DisplayName, 'datetime' => HistoryLog::DATETIME_REPLACE_STRING]);
                    } else {
                        $historyData[] = __('Restrict Bookers changed from :data1 to :data2 by :user on :datetime', ['data1' => $existingRestrictBookers->pluck('schedulingTeamName')->implode(', '), 'data2' => $newRestrictBookers->pluck('schedulingTeamName')->implode(', '), 'user' => $user->UD_DisplayName, 'datetime' => HistoryLog::DATETIME_REPLACE_STRING]);
                    }
                }
            }
            $facility->facilityRestrictBookers()->sync($facilityMainData['restrict_bookers_frm'] ?? []);

            //Facility location
            $facilityLocation = $editFacility != null ? $editFacility->location : new FacilityLocation();
            $facilityLocation->FCL_FacilityID = $facility->FC_FacilityID;
            if ($facility->FC_ProviderType == FacilityLocation::PROVIDER_TYPE_INTERNAL) {
                $facilityLocation->FCL_InternalLocationID = $facilityMainData['internal_location_frm'];
            }
            if ($facility->FC_ProviderType == FacilityLocation::PROVIDER_TYPE_EXTERNAL_UK) {
                $externalLocationUk = $facilityData['facilityExternalUkLocationForm'];
                $facilityLocation->FCL_BuildingNumberName_EXTUK = $externalLocationUk['location_external_uk_building_number'];
                $facilityLocation->FCL_Street_EXTUK = $externalLocationUk['location_external_uk_street'];
                $facilityLocation->FCL_City_EXTUK = $externalLocationUk['location_external_uk_city'];
                $facilityLocation->FCL_County_EXTUK = $externalLocationUk['location_external_uk_county'];
                $facilityLocation->FCL_Postcode_EXTUK = $externalLocationUk['location_external_uk_post_code'];
                $facilityLocation->FCL_Country_EXTUK = $externalLocationUk['location_external_uk_country'];
            }
            if ($facility->FC_ProviderType == FacilityLocation::PROVIDER_TYPE_EXTERNAL_INTERNATIONAL) {
                $facilityLocation->FCL_CompleteAddress_EXTINT = $facilityData['facilityExternalUkInternationalForm']['location_external_international_address'];
            }
            $facilityLocation->save();
            if (!empty($existingLocation) && $existingLocation != $facility->current_location) { //Location data history
                $historyData[] = __(
                    'Location Address changed from :data1 to :data2 by :user on :datetime',
                    [
                        'data1' => $existingLocation,
                        'data2' => $facility->current_location,
                        'user' => $user->UD_DisplayName,
                        'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                    ]
                );
            }

            //Linked facility
            $facilityLinkData = $facilityData['facilityLinkFacilityForm'] ?? [];
            $facilityLinkMandatory = $facilityLinkData['facility_link_mandatory'] ?? [];
            $history = $editFacility != null ? $this->facilityLinkHistory($facility, $facilityLinkData['facility_link_chose'] ?? [], $facilityLinkMandatory) : [];

            if (!empty($history)) {
                //Update existing facility bookings based on linked booking update
                $this->linkedFacilityUpdateFacilityBookings($facility, $facilityData, $user);
                if (count($history['from']) > 0 && count($history['to']) > 0) {
                    $historyData[] = __('Facility Link updated as :data1 to :data2 by :user on :datetime', [
                        'data1' => $history['from']->implode(', '),
                        'data2' => $history['to']->implode(', '),
                        'user' => $user->UD_DisplayName,
                        'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                    ]);
                } else {
                    $historyData[] = __('Facility Link :data1 :data2 by :user on :datetime', [
                        'data1' => count($history['from']) == 0 ? 'Added' : 'Removed',
                        'data2' => count($history['from']) == 0 ?  $history['to']->implode(', ') : $history['from']->implode(', '),
                        'user' => $user->UD_DisplayName,
                        'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                    ]);
                }
            }
            $facilityLink = [];
            foreach ($facilityLinkData['facility_link_chose'] ?? [] as $facilityLinkId) {
                $facilityLink[$facilityLinkId] = [
                    'FCLK_Mandatory' => (isset($facilityLinkMandatory) && in_array($facilityLinkId, $facilityLinkMandatory) ? 1 : 0)
                ];
            }
            $facility->linkedFacilities()->sync($facilityLink);

            //Facility Availability
            $history = [];
            $facilityAvailability = $editFacility != null ? $editFacility->facilityAvailability : new FacilityAvailability();
            $facilityAvailability->FCA_FacilityID = $facility->FC_FacilityID;
            $facilityAvailabilityData = $facilityData['facilityAvailability'];
            foreach (['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                $facilityAvailability->setAttribute('FCA_FacilityTimeFrom_' . $day, $facilityAvailabilityData['facility_availability_' . mb_strtolower($day) . '_from_frm']);
                $facilityAvailability->setAttribute('FCA_FacilityTimeTo_' . $day, $facilityAvailabilityData['facility_availability_' . mb_strtolower($day) . '_to_frm']);
                if (isset($facilityAvailabilityData['facility_unavailability_frm']) && in_array(mb_strtolower($day), $facilityAvailabilityData['facility_unavailability_frm'])) {
                    $facilityAvailability->setAttribute('FCA_FacilityTimeAvailability_' . $day, 0);
                } else {
                    $facilityAvailability->setAttribute('FCA_FacilityTimeAvailability_' . $day, 1);
                }

                //History
                if (!empty($editFacility)) {
                    $existingTimeTo = substr($facilityAvailability->getOriginal('FCA_FacilityTimeTo_' . $day), 0, 5);
                    $existingTimeFrom = substr($facilityAvailability->getOriginal('FCA_FacilityTimeFrom_' . $day), 0, 5);
                    $newTimeTo = substr($facilityAvailability->getAttribute('FCA_FacilityTimeTo_' . $day), 0, 5);
                    $newTimeFrom = substr($facilityAvailability->getAttribute('FCA_FacilityTimeFrom_' . $day), 0, 5);
                    if ($facilityAvailability->isDirty('FCA_FacilityTimeAvailability_' . $day)) {
                        $history[] = __(':day availability changed from :data1 to :data2', [
                            'day' => $day,
                            'data1' => $facilityAvailability->getOriginal('FCA_FacilityTimeAvailability_' . $day) ? 'Available' : 'Unavailable',
                            'data2' => $facilityAvailability->getAttribute('FCA_FacilityTimeAvailability_' . $day) ? 'Available' : 'Unavailable'
                        ]);
                    } elseif ($existingTimeTo != $newTimeTo || $existingTimeFrom != $newTimeFrom) {
                        $history[] = __(':day time changed from (:data1 - :data2) to (:data3 - :data4)', [
                            'day' => $day,
                            'data1' => $existingTimeFrom,
                            'data2' => $existingTimeTo,
                            'data3' => $newTimeFrom,
                            'data4' => $newTimeTo
                        ]);
                    }
                }
            }
            if (!empty($history)) {
                $historyData[] = __(
                    'Facility Availability - :changed by :user on :datetime',
                    [
                        'changed' => implode(', ', $history),
                        'user' => $user->UD_DisplayName,
                        'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                    ]
                );
            }
            $facilityAvailability->save();

            //Facility change booking type
            if ($editFacility != null) {
                $facilityChangeBookingType = $editFacility->facilityChangeBookingType != null ? $editFacility->facilityChangeBookingType : new FacilityChangeBookingType();
                $facilityChangeBookingTypeData = $facilityData['facilityChangeBookingType'];
                if ($facilityChangeBookingType->FCBT_FacilityChangeBookingTypeStartDate != null && empty($facilityChangeBookingTypeData['change_booking_type_start_date_frm'])) { //Delete if date is removed
                    $facilityChangeBookingType->delete();
                } elseif (!empty($facilityChangeBookingTypeData['change_booking_type_start_date_frm'])) {
                    $facilityChangeBookingType->FCBT_FacilityID = $facility->FC_FacilityID;
                    $facilityChangeBookingType->FCBT_FacilityChangeBookingTypeStartDate = Carbon::createFromFormat('d/m/Y', $facilityChangeBookingTypeData['change_booking_type_start_date_frm'])->format('Y-m-d');
                    $facilityChangeBookingType->FCBT_FacilityChangeBookingTypeEndDate = Carbon::createFromFormat('d/m/Y', $facilityChangeBookingTypeData['change_booking_type_end_date_frm'])->format('Y-m-d');
                    foreach (['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                        $facilityChangeBookingType->setAttribute('FCBT_FacilityChangeBookingTypeTimeFrom_' . $day, $facilityChangeBookingTypeData['facility_change_booking_type_' . mb_strtolower($day) . '_from_frm']);
                        $facilityChangeBookingType->setAttribute('FCBT_FacilityChangeBookingTypeTimeTo_' . $day, $facilityChangeBookingTypeData['facility_change_booking_type_' . mb_strtolower($day) . '_to_frm']);
                    }
                    $facilityChangeBookingType->save();
                }
            }

            //Facility mark as unavailable
            $history = [];
            $facilityMarkAsUnavailable = ($editFacility != null && $editFacility->facilityMarkAsUnavailable != null) ? $editFacility->facilityMarkAsUnavailable : new FacilityMarkUnavailable();
            $facilityMarkAsUnavailableData = $facilityData['facilityMarkAsUnavailable'];
            if ($facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableStartDate != null && empty($facilityMarkAsUnavailableData['mark_as_unavailable_start_date_frm'])) {
                //Delete if date is removed
                $facilityMarkAsUnavailable->delete();
                $history[] = __(' Removed ');
            }
            if (!empty($facilityMarkAsUnavailableData['mark_as_unavailable_start_date_frm'])) {
                $facilityMarkAsUnavailable->FMU_FacilityID = $facility->FC_FacilityID;
                $facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableStartDate = Carbon::createFromFormat('d/m/Y', $facilityMarkAsUnavailableData['mark_as_unavailable_start_date_frm'])->format('Y-m-d');
                $facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableEndDate = !empty($facilityMarkAsUnavailableData['mark_as_unavailable_end_date_frm']) ? Carbon::createFromFormat('d/m/Y', $facilityMarkAsUnavailableData['mark_as_unavailable_end_date_frm'])->format('Y-m-d') : null;
                //History
                if (
                    !empty($editFacility) && !empty($facilityMarkAsUnavailable->getOriginal('FMU_FacilityMarkUnavailableStartDate')) &&
                    (
                        $facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableStartDate != $facilityMarkAsUnavailable->getOriginal('FMU_FacilityMarkUnavailableStartDate')
                        ||
                        $facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableEndDate != $facilityMarkAsUnavailable->getOriginal('FMU_FacilityMarkUnavailableEndDate')
                    )
                ) {
                    $history[] = __('Date changed from (:data1 - :data2) to (:data3 - :data4)', [
                        'data1' => Carbon::parse($facilityMarkAsUnavailable->getOriginal('FMU_FacilityMarkUnavailableStartDate'))->format('d/m/Y'),
                        'data2' => Carbon::parse($facilityMarkAsUnavailable->getOriginal('FMU_FacilityMarkUnavailableEndDate'))->format('d/m/Y'),
                        'data3' => Carbon::parse($facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableStartDate)->format('d/m/Y'),
                        'data4' => Carbon::parse($facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableEndDate)->format('d/m/Y')
                    ]);
                } elseif (empty($facilityMarkAsUnavailable->getOriginal('FMU_FacilityMarkUnavailableStartDate'))) {
                    $startDate = Carbon::parse($facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableStartDate);
                    $endDate = $facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableEndDate
                        ? Carbon::parse($facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableEndDate)
                        : null;
                    $isFutureStart = $startDate->isAfter(Carbon::today());

                    if ($isFutureStart) {
                        $history[] = __('Facility will be automatically Unavailable from :data1', [
                            'data1' => $startDate->format('d/m/Y')
                        ]);
                    } else {
                        $history[] = __('Added :data1 to :data2', [
                            'data1' => $startDate->format('d/m/Y'),
                            'data2' => $endDate ? $endDate->format('d/m/Y') : 'indefinite'
                        ]);
                    }

                    if ($endDate) {
                        $history[] = __('Facility will automatically revert to Active after :data1', [
                            'data1' => $endDate->format('d/m/Y')
                        ]);
                    }
                }

                foreach (['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                    $facilityMarkAsUnavailable->setAttribute('FMU_FacilityMarkUnavailableTimeFrom_' . $day, $facilityMarkAsUnavailableData['facility_mark_as_unavailable_' . mb_strtolower($day) . '_from_frm'] ?? '00:00');
                    $facilityMarkAsUnavailable->setAttribute('FMU_FacilityMarkUnavailableTimeTo_' . $day, $facilityMarkAsUnavailableData['facility_mark_as_unavailable_' . mb_strtolower($day) . '_to_frm'] ?? '00:00');
                    if (isset($facilityMarkAsUnavailableData['facility_mark_as_unavailable_' . mb_strtolower($day) . '_is_checked_frm'])) {
                        $facilityMarkAsUnavailable->setAttribute(
                            'FMU_FacilityMarkUnavailableIsChecked_' . $day,
                            $facilityMarkAsUnavailableData['facility_mark_as_unavailable_' . mb_strtolower($day) . '_is_checked_frm'] == 'on' ? 1 : 0
                        );
                    } else {
                        $facilityMarkAsUnavailable->setAttribute('FMU_FacilityMarkUnavailableIsChecked_' . $day, 0);
                    }
                    //History
                    if (!empty($editFacility) && !empty($facilityMarkAsUnavailable->getOriginal('FMU_FacilityMarkUnavailableStartDate'))) {
                        $existingTimeTo = substr($facilityMarkAsUnavailable->getOriginal('FMU_FacilityMarkUnavailableTimeTo_' . $day), 0, 5);
                        $existingTimeFrom = substr($facilityMarkAsUnavailable->getOriginal('FMU_FacilityMarkUnavailableTimeFrom_' . $day), 0, 5);
                        $newTimeTo = substr($facilityMarkAsUnavailable->getAttribute('FMU_FacilityMarkUnavailableTimeTo_' . $day), 0, 5);
                        $newTimeFrom = substr($facilityMarkAsUnavailable->getAttribute('FMU_FacilityMarkUnavailableTimeFrom_' . $day), 0, 5);
                        if ($existingTimeTo != $newTimeTo || $existingTimeFrom != $newTimeFrom) {
                            $history[] = __(':day Unavailability time changed from (:data1 - :data2) to (:data3 - :data4)', [
                                'day' => $day,
                                'data1' => $existingTimeFrom,
                                'data2' => $existingTimeTo,
                                'data3' => $newTimeFrom,
                                'data4' => $newTimeTo
                            ]);
                        }
                    }
                }
                $facilityMarkAsUnavailable->save();
            }

            $conflictIds = request()->input('_conflict_ids', []);
            if (!empty($conflictIds)) {
                $facilityBookingRepo = app(\App\Repositories\Contracts\FacilityBookingRepositoryInterface::class);
                $facilityBookingRepo->declineConflictBookings($conflictIds, $user);
            }


            if (!empty($history)) {
                $historyData[] = __(
                    'Facility Unavailability - :changed by :user on :datetime',
                    [
                        'changed' => implode(', ', $history),
                        'user' => $user->UD_DisplayName,
                        'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                    ]
                );
            }

            //Facility Sub Type
            $facilitySubType = [];
            $history = $editFacility != null ? $this->facilitySubTypeHistory($facility, $facility->FC_FacilityTypeID, $facilityData['facilityTypeForm']) : [];
            if (!empty($history)) {
                $historyData[] = __('Facility Sub Type changed from :data1 to :data2 by :user on :datetime', [
                    'data1' => $history['from']->implode(', '),
                    'data2' => $history['to']->implode(', '),
                    'user' => $user->UD_DisplayName,
                    'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                ]);
            }
            $facilitySubTpes = FacilityType::find($facility->FC_FacilityTypeID)->facilitySubType;
            $facilitySubTypeData = $facilityData['facilityTypeForm'];
            foreach ($facilitySubTypeData['facility_sub_type_frm'] as $facilitySTD) {
                if ($facilitySubTpes->where('FST_FacilitySubTypeID', $facilitySTD)->count() == 0) {
                    continue;
                }
                $facilitySubType[$facilitySTD]['FCST_PrimarySubType'] = ($facilitySubTypeData['facility_sub_type_primary_frm'] == $facilitySTD ? 1 : 0);
            }
            $facility->facilitySubTypes()->sync($facilitySubType);
            //Update Facility sub type in facility bookings
            if (!empty($history)) {
                $this->updateFacilityBookingSubType($facility, $facilitySubType);
            }

            //Facility Technical setup
            $facilityTechnicalSetupData = $facilityData['facilityTechnicalSetupForm'];
            if ($editFacility != null) { // History of services
                $existingServices = $facility->facilityServices;
                $newServices = Service::whereIn('SR_ServiceID', $facilityTechnicalSetupData['service_list_select_to'])->get();
                if ($existingServices->diff($newServices)->count() > 0 || $newServices->diff($existingServices)->count() > 0) {
                    $historyData[] = __('Service changed from :data1 to :data2 by :user on :datetime', [
                        'data1' => $existingServices->pluck('SR_Service')->implode(', '),
                        'data2' => $newServices->pluck('SR_Service')->implode(', '),
                        'user' => $user->UD_DisplayName,
                        'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                    ]);
                }
            }
            $facility->facilityServices()->sync($facilityTechnicalSetupData['service_list_select_to']); //add services


            $history = $editFacility != null ? $this->equipmentHistory($facility, collect($facilityTechnicalSetupData['equipment'])) : [];
            if (!empty($history)) {
                $historyData[] = __('Equipment changed from :data1 to :data2 by :user on :datetime', [
                    'data1' => $history['from']->implode(', '),
                    'data2' => $history['to']->implode(', '),
                    'user' => $user->UD_DisplayName,
                    'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                ]);
            }
            $facilityEquipments = [];
            foreach ($facilityTechnicalSetupData['equipment'] as $facilityEquipment) {
                $facilityEquipments[$facilityEquipment['equipment_id']] = [
                    'FCEQ_Quantity' => $facilityEquipment['quantity'],
                    'FCEQ_Note' => $facilityEquipment['note']
                ];
            }
            $facility->facilityEquipments()->sync($facilityEquipments); // add equipments

            if ($editFacility == null) { // create facility history
                $historyData[] = __('Facility created by :user on :datetime', [
                    'user' => $user->UD_DisplayName,
                    'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                ]);
            }
            //Save history
            $this->storeFacilityHistory($facility, $historyData, $user);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return $facility;
    }

    /**
     * Update Facility booking sub type
     * @param Facility $facility facility
     * @param array $facilitySubType new subtypes
     */
    private function updateFacilityBookingSubType(Facility $facility, array $facilitySubType)
    {
        $primaryFacilitySubType = null;
        foreach ($facilitySubType as $subTypeId => $data) {
            if ($data['FCST_PrimarySubType'] == 1) {
                $primaryFacilitySubType = $subTypeId;
            }
        }
        if ($primaryFacilitySubType == null) {
            return;
        }
        $facility->facilityBookings()->whereNotIn('FB_FacilitySubTypeID', array_keys($facilitySubType))->update([
            'FB_FacilitySubTypeID' => $primaryFacilitySubType
        ]);
    }

    /**
     * Update linked facility booking based on facility linked facility update
     * @param Facility $facility
     * @param array $facilityFormData
     */
    private function linkedFacilityUpdateFacilityBookings(Facility $facility, array $facilityFormData, User $user)
    {
        $facilityBookingRepository = app(FacilityBookingRepositoryInterface::class);
        $newLinkedFacilities = $facilityFormData['facilityLinkFacilityForm']['facility_link_chose'] ?? [0];
        $removedLinkedFacilities = $facility->linkedFacilities->whereNotIn('FC_FacilityID', $newLinkedFacilities);

        //Deleted linked facility remove bookings
        if ($removedLinkedFacilities->count() > 0 && $facilityFormData['facility_booking_linked_decline_reason'] == 1) {
            $linkedFacilityQuery = $facility->facilityBookings()->linkedBooking()
                ->whereNotIn('linkedBookings.FB_BookingStatus', [FacilityBooking::BOOKING_STATUS_DECLINED, FacilityBooking::BOOKING_STATUS_CANCELLED])
                ->whereDate('FacilityBookings.FB_BookingStartDateTime', '>=', Carbon::now());
            $linkedFacilityBookingIds = $linkedFacilityQuery
                ->where('linkedBookings.FB_FacilityID', $removedLinkedFacilities->pluck('FC_FacilityID')->toArray())
                ->select('linkedBookings.FB_FacilityBookingID')->distinct()->get()->toArray();
            FacilityBooking::whereIn('FB_FacilityBookingID', $linkedFacilityBookingIds)->delete();
        }

        //Add new mandatory bookings
        $mandatoryLinkedFacilities = [];
        foreach ($facilityFormData['facilityLinkFacilityForm']['facility_link_chose'] ?? [] as $facilityLink) {
            if (in_array($facilityLink, $facilityFormData['facilityLinkFacilityForm']['facility_link_mandatory'] ?? [])) {
                $mandatoryLinkedFacilities[] = $facilityLink;
            }
        }
        if (!empty($mandatoryLinkedFacilities)) {
            $mandatoryFacilities = Facility::whereIn('FC_FacilityID', $mandatoryLinkedFacilities)->get();
            $facilityBookings = FacilityBooking::where('FB_FacilityID', $facility->FC_FacilityID)->whereNotIn('FB_BookingStatus', [FacilityBooking::BOOKING_STATUS_DECLINED, FacilityBooking::BOOKING_STATUS_CANCELLED])
                ->whereDate('FB_BookingStartDateTime', '>=', Carbon::now()->startOfDay())->get();
            $mandatoryLinkedFacilityExistingBookings = FacilityBooking::whereIn('FB_FacilityID', $mandatoryLinkedFacilities)
                ->whereDate('FB_BookingStartDateTime', '>=', Carbon::now()->startOfDay())
                ->whereIn('FB_FacilityBookingRecurrenceID', $facilityBookings->pluck('FB_FacilityBookingRecurrenceID')->toArray())
                ->get();

            //Now create bookings
            foreach ($facilityBookings as $primaryBooking) {
                $newBookingIds = [];
                foreach ($mandatoryFacilities as $mandatoryLinkedFacility) {
                    $exists = $mandatoryLinkedFacilityExistingBookings->contains(function ($otherBooking) use ($primaryBooking, $mandatoryLinkedFacility) {
                        return
                            // Same recurrence
                            ($otherBooking->FB_FacilityBookingRecurrenceID == $primaryBooking->FB_FacilityBookingRecurrenceID)
                            // Same calendar date
                            && $otherBooking->FB_BookingStartDateTime->isSameDay($primaryBooking->FB_BookingStartDateTime)
                            && $mandatoryLinkedFacility->FC_FacilityID == $otherBooking->FB_FacilityID;
                    });
                    if ($exists) {
                        continue;
                    }
                    $newLinkedBooking = $primaryBooking->replicate();
                    $newLinkedBooking->FB_FacilityID = $mandatoryLinkedFacility->FC_FacilityID;
                    $newLinkedBooking->FB_Private = $mandatoryLinkedFacility->FC_MakeAllBookingsPrivate;
                    $newLinkedBooking->FB_BookingStatus = FacilityBooking::BOOKING_STATUS_CONFIRMED;
                    $newLinkedBooking->save();
                    $newBookingIds[] = $newLinkedBooking->FB_FacilityBookingID;

                    //History
                    $historyData = [];
                    $historyData[] = __('Facility Booking created by :user on :datetime', [
                        'user' => $user->UD_DisplayName,
                        'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                    ]);
                    $facilityBookingRepository->storeFacilityBookingHistory($newLinkedBooking, $historyData, $user);
                }
                if (!empty($newBookingIds)) {
                    $primaryBooking->linkedFacilityBookings()->attach($newBookingIds);
                }
            }
        }
    }

    /*
    ** Soft Delete Repository
    */
    public function destroyFacility($facility, User $user): bool
    {
        $facility_id = Facility::find($facility->FC_FacilityID);
        $booking_exist = FacilityBooking::where('FB_FacilityID', $facility->FC_FacilityID)->count();
        if ($booking_exist > 0) {
            return 0;
        } else {
            $facility_id->delete();
            return true;
        }
    }

    /**
     * Archive facility
     */
    public function archiveFacility($archiveData, $facility, User $user): bool|string
    {
        $archive_from_date =  Carbon::createFromFormat('d/m/Y', $archiveData['change_date'])->format('Y-m-d');
        $change_status =  $archiveData['change_status'];
        if ($change_status == "archive") {
            $unavailability = $facility->facilityMarkAsUnavailable;
            if ($unavailability != null) {
                $unavailableStartDate = $unavailability->FMU_FacilityMarkUnavailableStartDate;
                $unavailableEndDate = $unavailability->FMU_FacilityMarkUnavailableEndDate;
                $archiveDate = Carbon::parse($archive_from_date);
                if (
                    $unavailableStartDate != null
                    && $archiveDate->greaterThanOrEqualTo($unavailableStartDate)
                    && ($unavailableEndDate == null || $archiveDate->lessThanOrEqualTo($unavailableEndDate))
                ) {
                    return 'unavailable_conflict';
                }
            }

            $booking_exist = FacilityBooking::where('FB_FacilityID', $facility->FC_FacilityID)->where('FB_BookingStartDateTime', '>', $archive_from_date)->whereNotIn('FB_BookingStatus', [FacilityBooking::BOOKING_STATUS_DECLINED, FacilityBooking::BOOKING_STATUS_CANCELLED])->count();
            if ($booking_exist > 0) {
                $return_value = 0;
            } else {
                $facility->FC_ArchivedDate = $archive_from_date;
                $facility->save();
                $return_value = true;
                $archiveDateCarbon = Carbon::createFromFormat('d/m/Y', $archiveData['change_date']);
                $isFutureDate = $archiveDateCarbon->isAfter(Carbon::today());
                $historyMessage = $isFutureDate
                    ? sprintf('Facility will be automatically Archived from %s by %s on %s', $archiveData['change_date'], $user->UD_DisplayName, HistoryLog::DATETIME_REPLACE_STRING)
                    : sprintf('Facility is Archived from %s by %s on %s', $archiveData['change_date'], $user->UD_DisplayName, HistoryLog::DATETIME_REPLACE_STRING);
                $this->storeFacilityHistory(
                    $facility,
                    [$historyMessage],
                    $user
                );
            }
            return $return_value;
        } else {
            $facility->FC_ArchivedDate = null;
            $facility->FC_ActiveFrom =  $archive_from_date;
            $facility->save();

            $this->storeFacilityHistory(
                $facility,
                [
                    sprintf('Facility is Activated from %s by %s on %s', $archiveData['change_date'], $user->UD_DisplayName, HistoryLog::DATETIME_REPLACE_STRING)
                ],
                $user
            );
            return true;
        }
    }

    public function updateFacilityBookings($facility, $status, $startDate, $endDate, $conflictIds, $actor)
    {
        $bookings = $facility->facilityBookings()
            ->whereIn('FB_BookingStatus', [
                FacilityBooking::BOOKING_STATUS_NEW,
                FacilityBooking::BOOKING_STATUS_PENDING
            ])
            ->whereBetween('FB_BookingStartDateTime', [$startDate, $endDate])
            ->whereNotIn('FB_FacilityBookingID', $conflictIds)
            ->get();

        if ($bookings->isEmpty()) {
            return;
        }

        $bookings->each(function ($booking) use ($status) {
            $booking->FB_BookingStatus = $status;
            $booking->save(); // fires updating/updated events and updates timestamps
        });

        $facilityBookingRepo = app(\App\Repositories\Contracts\FacilityBookingRepositoryInterface::class);
        $facilityBookingRepo->sendUpdateMail($bookings, $actor);
    }
    /**
     * Generate equipment history data
     * @param Facility $editedFacility
     * @param Collection $requestData
     */
    private function equipmentHistory(Facility $editedFacility, Collection $requestData): array
    {
        $existingEquipments = $editedFacility->facilityEquipments;
        $stringMaker = function ($name, $quantity, $note) {
            return $name . ' (Quantity - ' . $quantity . ' | Note - ' . $note . ') ';
        };
        $existingData = [];
        foreach ($existingEquipments as $existingEquipment) {
            $existingData[] = $stringMaker($existingEquipment->EQ_Equipment, $existingEquipment->getOriginal('pivot_FCEQ_Quantity'), $existingEquipment->getOriginal('pivot_FCEQ_Note'));
        }
        $newData = [];
        $newEquipments = Equipment::whereIn('EQ_EquipmentID', $requestData->pluck('equipment_id')->toArray())->get();
        foreach ($newEquipments as $newEquipment) {
            $newData[] = $stringMaker($newEquipment->EQ_Equipment, $requestData->where('equipment_id', $newEquipment->EQ_EquipmentID)->first()['quantity'], $requestData->where('equipment_id', $newEquipment->EQ_EquipmentID)->first()['note']);
        }
        $existingData = collect($existingData);
        $newData = collect($newData);
        return ($existingData->diff($newData)->count() > 0 || $newData->diff($existingData)->count()) ? ['from' => $existingData, 'to' => $newData] : [];
    }

    /**
     * Generate Facility Sub type history data
     * @param Facility $editedFacility
     * @param int $facilityTypeId
     * @param array $requestData
     */
    private function facilitySubTypeHistory(Facility $editedFacility, int $facilityTypeId, array $requestData): array
    {
        $existingFacilitySubTypes = $editedFacility->facilitySubTypes;
        $stringMaker = function ($name, $primary) {
            return $name . ' (Primary - ' . ($primary ? 'Yes' : 'No') . ') ';
        };
        $existingData = [];
        foreach ($existingFacilitySubTypes as $existingFacilitySubTypes) {
            $existingData[] = $stringMaker($existingFacilitySubTypes->FST_FacilitySubType, $existingFacilitySubTypes->getOriginal('pivot_FCST_PrimarySubType'));
        }
        $newFacilitySubTypes = FacilitySubType::whereIn('FST_FacilitySubTypeID', $requestData['facility_sub_type_frm'])->where('FST_FacilityTypeID', $facilityTypeId)->get();
        foreach ($newFacilitySubTypes as $newFacilitySubType) {
            $newData[] = $stringMaker($newFacilitySubType->FST_FacilitySubType, ($requestData['facility_sub_type_primary_frm'] == $newFacilitySubType->FST_FacilitySubTypeID));
        }
        $existingData = collect($existingData);
        $newData = collect($newData);
        return ($existingData->diff($newData)->count() > 0 || $newData->diff($existingData)->count()) ? ['from' => $existingData, 'to' => $newData] : [];
    }

    /**
     * Generate Facility link history data
     *
     * @param Facility $editedFacility
     * @param array $facilityLinkIds
     * @param array $facilityLinkMandatory
     */
    private function facilityLinkHistory(Facility $editedFacility, array $facilityLinkIds, array $facilityLinkMandatory): array
    {
        $existingFacilityLinks = $editedFacility->linkedFacilities;
        $stringMaker = function ($name, $mandatory) {
            return $name . ' (Mandatory - ' . ($mandatory ? 'Yes' : 'No') . ') ';
        };
        $existingData = [];
        foreach ($existingFacilityLinks as $existingFacilityLink) {
            $existingData[] = $stringMaker($existingFacilityLink->FC_FacilityName, $existingFacilityLink->getOriginal('pivot_FCLK_Mandatory'));
        }
        $newFacilityLinks = Facility::whereIn('FC_FacilityID', $facilityLinkIds)->get();
        $newData = [];
        foreach ($newFacilityLinks as $newFacilityLink) {
            $newData[] = $stringMaker($newFacilityLink->FC_FacilityName, (in_array($newFacilityLink->FC_FacilityID, $facilityLinkMandatory)));
        }
        $existingData = collect($existingData);
        $newData = collect($newData);
        return ($existingData->diff($newData)->count() > 0 || $newData->diff($existingData)->count()) ? ['from' => $existingData, 'to' => $newData] : [];
    }

    /**
     * Store Facility history
     *
     * @param Facility $facility
     * @param array $facilityHistoryLog
     * @param User $user
     */
    private function storeFacilityHistory(Facility $facility, array $facilityHistoryLog, User $user): ?HistoryLog
    {
        if (empty($facilityHistoryLog)) {
            return null;
        }
        $historyLog = new HistoryLog();
        $historyLog->HL_HLogs = $facilityHistoryLog;
        $historyLog->HL_Created_BY = $user->UD_UserID;
        $historyLog->HL_Type = HistoryLog::FACILITY_HISTORY;
        $facility->history()->save($historyLog);
        return $historyLog;
    }

    /**
     * Get list of areas
     */
    public function facilityAreaList(): array
    {
        return Division::select('DivisionID', 'DivisionName')->where('isActive', 1)->get()->toArray();
    }

    /**
     * Get booking count by after date with new and pending status
     */
    public function countFutureBookingsByStatus($facility, $from, $to): int
    {
        $startBookingDate = now()->addDays($from);
        $endBookingDate   = now()->addDays($to)->endOfDay();

        return  $facility->facilityBookings()->whereIn('FB_BookingStatus', [
            FacilityBooking::BOOKING_STATUS_NEW,
            FacilityBooking::BOOKING_STATUS_PENDING
        ])->whereBetween('FB_BookingStartDateTime', [$startBookingDate, $endBookingDate])->count();
    }

    /**
     * Update Facility Administrator
     *
     * @param Facility $facility
     * @param array $formData
     */
    public function facilityAdministratorStore(Facility $facility, array $formData, User $user): Facility
    {
        DB::beginTransaction();
        try {
            $facilityRoleLink = [];
            foreach ($formData['facility_admins'] ?? [] as $facilityAdmin) {
                $facilityRoleLink[$facilityAdmin] = [
                    'FUR_Role' => Facility::FACILITY_ROLE_ADMINISTRATOR,
                    'FUR_CreatedBy' => $user->UD_UserID,
                    'FUR_CreatedDate' => Carbon::now(),
                    'FUR_UpdatedBy'  => $user->UD_UserID,
                    'FUR_UpdatedDate' => Carbon::now()
                ];
            }
            // History of Facility Administrator
            $historyData = [];
            $existingFacilityAdmins = $facility->facilityAdministrators;
            $newFacilityAdmins = User::whereIn('UD_UserID', $formData['facility_admins'] ?? [0])->get();
            if ($existingFacilityAdmins->diff($newFacilityAdmins)->count() > 0 || $newFacilityAdmins->diff($existingFacilityAdmins)->count() > 0) {
                if (empty($formData['facility_admins'] ?? [])) {
                    $historyData[] = __('Facility Administrator removed :data1 by :user on :datetime', ['data1' => $existingFacilityAdmins->pluck('UD_DisplayName')->implode(', '), 'user' => $user->UD_DisplayName, 'datetime' => HistoryLog::DATETIME_REPLACE_STRING]);
                } elseif ($existingFacilityAdmins->count() == 0) {
                    $historyData[] = __('Facility Administrator updated as :data2 by :user on :datetime', ['data2' => $newFacilityAdmins->pluck('UD_DisplayName')->implode(', '), 'user' => $user->UD_DisplayName, 'datetime' => HistoryLog::DATETIME_REPLACE_STRING]);
                } else {
                    $historyData[] = __('Facility Administrator changed from :data1 to :data2 by :user on :datetime', ['data1' => $existingFacilityAdmins->pluck('UD_DisplayName')->implode(', '), 'data2' => $newFacilityAdmins->pluck('UD_DisplayName')->implode(', '), 'user' => $user->UD_DisplayName, 'datetime' => HistoryLog::DATETIME_REPLACE_STRING]);
                }
                //Send Email
                Mail::to($facility->facility_contact_emails)
                    ->cc($user->UD_InternalEmail)
                    ->send(new FacilityAdministratorUpdatedMail($facility, $user, $existingFacilityAdmins, $newFacilityAdmins));
            }
            $facility->facilityAdministrators()->sync($facilityRoleLink);
            //Save history
            $this->storeFacilityHistory($facility, $historyData, $user);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return $facility;
    }

    /**
     * Normalize a list of comma-separated email addresses.
     * @param  string|null  $emails  Comma-separated email addresses
     * @return array        Normalized array of unique email addresses
     */
    public function normalizeEmails($emails)
    {
        return collect(explode(',', (string) $emails))
            ->map(fn($email) => strtolower(trim($email)))
            ->filter()->unique()->sort()
            ->values()->toArray();
    }
}
