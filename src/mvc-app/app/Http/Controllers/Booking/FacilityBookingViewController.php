<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Facility\Action;
use App\Models\Facility\Facility;
use App\Models\FacilityBooking\ExternalCustomer;
use App\Models\FacilityBooking\FacilityBookerNote;
use App\Models\FacilityBooking\FacilityBooking;
use App\Http\Requests\CancelFacilityBookingRequest;
use App\Trait\WebSocketEventTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class FacilityBookingViewController extends Controller
{
    use WebSocketEventTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return View::make("pages.facility-booking.facility-booking");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Facility $facility, Request $request)
    {
        return View::make("pages.facility-booking.form.facility-booking-form", [
            'facility' => $facility,
            'actions' => Action::all(),
            'externalCustomers' => ExternalCustomer::getListOfExternalCustomers(),
            'viewOnly' => false,
            'dateSelected' =>  $request->date_selected,
            'adminAccess' => Auth::user()->haveAccessToFacility($facility),
            'clickPositionDateTime' => Carbon::parse($request->click_position_datetime)
        ]);
    }

    /**
     * Show the form for creating a new facility record.
     *
     * @return \Illuminate\Http\Response
     */
    public function createRecord(Facility $facility, Request $request)
    {
        return View::make("pages.facility-booking.form.facility-booking-form", [
            'facility' => $facility,
            'actions' => Action::all(),
            'externalCustomers' => ExternalCustomer::getListOfExternalCustomers(),
            'createFacilityRecord' => true,
            'viewOnly' => false,
            'dateSelected' =>  $request->date_selected,
            'adminAccess' => Auth::user()->haveAccessToFacility($facility),
            'clickPositionDateTime' => Carbon::parse($request->click_position_datetime)
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(FacilityBooking $facilityBooking)
    {
        return View::make("pages.facility-booking.form.facility-booking-view-form", [
            'facility' => $facilityBooking->facility,
            'facilityBooking' => $facilityBooking,
            'actions' => Action::all(),
            'externalCustomers' => ExternalCustomer::getListOfExternalCustomers($facilityBooking),
            'viewOnly' => true,
            'facilityBookingRecurrence' => $facilityBooking->facilityBookingRecurrence,
            'adminAccess' => Auth::user()->haveAccessToFacility($facilityBooking->facility)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param FacilityBooking $facilityBooking
     *
     * @return View
     */
    public function edit(FacilityBooking $facilityBooking)
    {
        return View::make("pages.facility-booking.form.facility-booking-form", [
            'facility' => $facilityBooking->facility,
            'facilityBooking' => $facilityBooking,
            'facilityBookingRecurrence' => $facilityBooking->facilityBookingRecurrence,
            'actions' => Action::all(),
            'externalCustomers' => ExternalCustomer::getListOfExternalCustomers($facilityBooking),
            'viewOnly' => false,
            'availableRecurrenceFacilityBookingCount' => $facilityBooking->facilityBookingRecurrence->availableFacilityBookings(true)->count(),
            'adminAccess' => Auth::user()->haveAccessToFacility($facilityBooking->facility)
        ]);
    }

    /**
     * Copy facility booking
     */
    public function copyFacilityBooking(FacilityBooking $facilityBooking, Request $request)
    {
        //If deleted, blank the external related fields for the copy
        if ($facilityBooking->externalCustomer?->deleted_at != null) {
            $facilityBooking->FB_ExternalCustomerID = null;
            $facilityBooking->FB_ContactName = '';
            $facilityBooking->FB_ContactTelephone = '';
            $facilityBooking->FB_ContactEmail = '';
        }

        return View::make("pages.facility-booking.form.facility-booking-form", [
            'facility' => $facilityBooking->facility,
            'facilityBooking' => $facilityBooking->setAttribute('FB_BookingEndDateTime', Carbon::parse($request->click_position_datetime)->addMinutes($facilityBooking->duration_minutes))
                ->setAttribute('FB_BookingStartDateTime', Carbon::parse($request->click_position_datetime)),
            'actions' => Action::all(),
            'externalCustomers' => ExternalCustomer::all(),
            'viewOnly' => false,
            'facilityBookingRecurrence' => $facilityBooking->facilityBookingRecurrence,
            'adminAccess' => Auth::user()->haveAccessToFacility($facilityBooking->facility),
            'copyBookingDate' => $request->copy_date,
            'createFacilityRecord' => $request->create_booking_record == 1 ? true : false
        ]);
    }

    /**
     * Move facility booking
     */
    public function moveFacilityBooking(FacilityBooking $facilityBooking, Request $request)
    {
        return View::make("pages.facility-booking.form.facility-booking-form", [
            'facility' => Facility::find($request->facility_id),
            'facilityBooking' => $facilityBooking->setAttribute('FB_BookingEndDateTime', Carbon::parse($request->click_position_datetime)->addMinutes($facilityBooking->duration_minutes))
                ->setAttribute('FB_BookingStartDateTime', Carbon::parse($request->click_position_datetime)),
            'actions' => Action::all(),
            'facilityBookingRecurrence' => $facilityBooking->facilityBookingRecurrence,
            'externalCustomers' => ExternalCustomer::getListOfExternalCustomers($facilityBooking),
            'viewOnly' => false,
            'adminAccess' => Auth::user()->haveAccessToFacility(Facility::find($request->facility_id)),
            'moveBookingDate' => $request->move_date,
            'availableRecurrenceFacilityBookingCount' => $facilityBooking->facilityBookingRecurrence->availableFacilityBookings()->count(),
            'createFacilityRecord' => $request->create_booking_record == 1 ? true : false
        ]);
    }

    /**
     * Gets create booker note form
     *
     * @param Facility $facility
     * @param Request  $request
     */
    public function createBookerNote(Facility $facility, Request $request)
    {
        return View::make("pages.facility-booking.form.facility-booker-note-form", [
            'facility' => $facility,
            'clickPositionDatetime' =>  Carbon::parse($request->click_position_datetime)
        ]);
    }

    /**
     * Gets edit booker note form
     *
     * @param FacilityBookerNote $facilityBookerNote
     */
    public function editBookerNote(FacilityBookerNote $facilityBookerNote)
    {
        return View::make("pages.facility-booking.form.facility-booker-note-form", [
            'facility' => $facilityBookerNote->facility,
            'facilityBookerNote' => $facilityBookerNote,
            'facilityBookerNoteRecurrence' => $facilityBookerNote->facilityBookerNoteRecurrence
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  facilityBooking $facilityBooking
     * @return \Illuminate\Http\Response
     */
    public function cancel_popup(FacilityBooking $facilityBooking, CancelFacilityBookingRequest $request)
    {
        return View::make("pages.facility-booking.form.facility-booking-cancel", [
            'facilityBooking' => $facilityBooking
        ]);
    }

    /**
     * Reinstate the specified resource from storage.
     *
     * @param  facilityBooking $facilityBooking
     * @return \Illuminate\Http\Response
     */
    public function reinstatePopup(FacilityBooking $facilityBooking)
    {
        return View::make("pages.facility-booking.form.facility-booking-reinstate-option", [
            'facilityBooking' => $facilityBooking,
            'availableRecurrenceFacilityBookingCount' => $facilityBooking->facilityBookingRecurrence->availableFacilityBookings(true)->count(),
        ]);
    }

    /**
     * Get facility booking history.
     *
     * @param  Facility $facilityBooking
     * @return \Illuminate\Http\Response
     */
    public function history(FacilityBooking $facilityBooking)
    {
        return View::make("pages.history.history-view", [
            'title' => __('Facility Booking :facilityBooking History', ['facilityBooking' => $facilityBooking->FB_BookingTitle]),
            'historyLogs' => $facilityBooking->history()->orderBy('HL_ID', 'DESC')->get()
        ]);
    }

    /**
     * Get scheduler note history.
     *
     * @param  FacilityBookerNote $facilityBookerNote
     *
     * @return View
     */
    public function bookerNotehistory(FacilityBookerNote $facilityBookerNote)
    {
        return View::make("pages.history.history-view", [
            'title' => __('Scheduler Note History'),
            'historyLogs' => $facilityBookerNote->history()->orderBy('HL_ID', 'DESC')->get()
        ]);
    }
}
