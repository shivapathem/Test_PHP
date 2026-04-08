<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFacilityBookingRequest;
use App\Http\Requests\UpdateFacilityBookingRequest;
use App\Http\Requests\CancelFacilityBookingRequest;
use App\Http\Requests\CancelFacilityBookingPreviewRequest;
use App\Http\Requests\StoreBookerNoteRequest;
use App\Http\Requests\StoreFacilityBookingRecordRequest;
use App\Http\Requests\UpdateBookerNoteRequest;
use App\Http\Requests\DeleteFacilityBookingRequest;
use App\Http\Requests\MoveFacilityBookingRequest;
use App\Http\Requests\ReinstateFacilityBooking;
use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBookerNote;
use App\Models\FacilityBooking\FacilityBooking;
use App\Models\FacilityBooking\FacilityBookingRecurrence;
use App\Repositories\Contracts\FacilityBookingRepositoryInterface;
use App\Trait\WebSocketEventTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacilityBookingApiController extends Controller
{
    use WebSocketEventTrait;

    /**
     * @var FacilityBookingRepository
     */
    protected $facilityBookingRepository;

    /**
     * FacilityBookingApiController constructor.
     *
     * @param FacilityBookingRepositoryInterface $facilityBookingRepository facility booking repo
     */
    public function __construct(FacilityBookingRepositoryInterface $facilityBookingRepository)
    {
        $this->facilityBookingRepository = $facilityBookingRepository;
    }

    /**
     * This creates a booking record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeRecord(Facility $facility, StoreFacilityBookingRecordRequest $request)
    {
        $request->merge(['facility_booking_data_type' => FacilityBookingRecurrence::BOOKING_TYPE_RECORD]);
        $this->facilityBookingRepository->saveFacilityBookingRecurrence($facility, $request->all(), Auth::user());
        $facilityIds = $facility->linkedFacilities->pluck('FC_FacilityID')->toArray();
        $facilityIds[] = $facility->FC_FacilityID;
        $this->triggerFacilityUpdateEvent($facility);
        return response()->json(
            $this->facilityBookingRepository->getFacilityBookings(
                Carbon::parse($request->facilityBookingListSetting['start_date']),
                Carbon::parse($request->facilityBookingListSetting['end_date']),
                $request->facilityBookingListSetting['show_canceled'] ?? 0,
                $request->facilityBookingListSetting['show_only'] ?? [],
                $facilityIds
            )
        );
    }

    /**
     * This creates a facility booking request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Facility $facility, StoreFacilityBookingRequest $request)
    {
        $this->facilityBookingRepository->saveFacilityBookingRecurrence($facility, $request->all(), Auth::user());
        $facilityIds = $facility->linkedFacilities->pluck('FC_FacilityID')->toArray();
        $facilityIds[] = $facility->FC_FacilityID;
        $this->triggerFacilityUpdateEvent($facility);
        return response()->json(
            $this->facilityBookingRepository->getFacilityBookings(
                Carbon::parse($request->facilityBookingListSetting['start_date']),
                Carbon::parse($request->facilityBookingListSetting['end_date']),
                $request->facilityBookingListSetting['show_canceled'] ?? 0,
                $request->facilityBookingListSetting['show_only'] ?? [],
                $facilityIds
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param FacilityBooking $facilityBooking facility booking
     * @param  UpdateFacilityBookingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(FacilityBooking $facilityBooking, UpdateFacilityBookingRequest $request)
    {
        $this->facilityBookingRepository->updateFacilityBooking($facilityBooking, $request->all(), Auth::user());
        $facilityIds = $facilityBooking->facility->linkedFacilities->pluck('FC_FacilityID')->toArray();
        $facilityIds[] = $facilityBooking->facility->FC_FacilityID;
        $this->triggerFacilityUpdateEvent($facilityBooking->facility);
        if (!isset($request->facilityBookingListSetting)) {
            return response()->json(['Booking updated successfully.']);
        }
        return response()->json(
            $this->facilityBookingRepository->getFacilityBookings(
                Carbon::parse($request->facilityBookingListSetting['start_date']),
                Carbon::parse($request->facilityBookingListSetting['end_date']),
                $request->facilityBookingListSetting['show_canceled'] ?? 0,
                $request->facilityBookingListSetting['show_only'] ?? [],
                $facilityIds
            )
        );
    }

    /**
     * Move the specified resource in storage.
     *
     * @param Facility $facility Facility
     * @param FacilityBooking $facilityBooking facility booking
     * @param  MoveFacilityBookingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function moveBooking(Facility $facility, FacilityBooking $facilityBooking, MoveFacilityBookingRequest $request)
    {
        //Facilities before move
        $facilityIds = $facilityBooking->facility->linkedFacilities->pluck('FC_FacilityID')->toArray();
        $facilityIds[] = $facilityBooking->facility->FC_FacilityID;
        //Move
        $this->facilityBookingRepository->updateFacilityBooking($facilityBooking, $request->all(), Auth::user());
        $facilityBooking->refresh();
        //Facilities after move
        $facilityIds = array_merge($facilityIds, $facilityBooking->facility->linkedFacilities->pluck('FC_FacilityID')->toArray());
        $facilityIds[] = $facilityBooking->facility->FC_FacilityID;
        $this->triggerFacilityUpdateEvent($facilityBooking->facility);
        return response()->json(
            $this->facilityBookingRepository->getFacilityBookings(
                Carbon::parse($request->facilityBookingListSetting['start_date']),
                Carbon::parse($request->facilityBookingListSetting['end_date']),
                $request->facilityBookingListSetting['show_canceled'] ?? 0,
                $request->facilityBookingListSetting['show_only'] ?? [],
                $facilityIds
            )
        );
    }

    /**
     * Store booker note
     * @param Facility $facility
     * @param StoreBookerNoteRequest $request
     */
    public function storeBookerNote(Facility $facility, StoreBookerNoteRequest $request)
    {
        $this->triggerFacilityUpdateEvent($facility);
        $this->facilityBookingRepository->saveFacilityBookerNoteRecurrence($facility, $request->all(), Auth::user());
        return response()->json(['Record added successfully']);
    }

    /**
     * Edit booker note
     *
     * @param FacilityBookerNote $facilityBookerNote
     * @param StoreBookerNoteRequest $request
     */
    public function updateBookerNote(FacilityBookerNote $facilityBookerNote, UpdateBookerNoteRequest $request)
    {
        $this->facilityBookingRepository->updateFacilityBookerNote($facilityBookerNote, $request->all(), Auth::user());
        return response()->json(['Booker Note updated successfully']);
    }

    /**
     * Gets the list of facility details and its bookings
     *
     * @param Request $request
     */
    public function getFacilityBookings(Request $request)
    {
        $facilityIds = [];
        if ($request->facilityId) {
            $facility = Facility::find($request->facilityId);
            $facilityIds = $facility->linkedFacilities->pluck('FC_FacilityID')->toArray();
            $facilityIds[] = $facility->FC_FacilityID;
        }
        return response()->json(
            $this->facilityBookingRepository->getFacilityBookings(
                Carbon::parse($request->start_date),
                Carbon::parse($request->end_date),
                $request->show_canceled,
                $request->show_only ?? [],
                $facilityIds
            )
        );
    }

    /**
     * cancel the specified resource in storage.
     *
     * @param FacilityBooking $facilityBooking facility booking
     * @param  CancelFacilityBookingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function cancel(FacilityBooking $facilityBooking, CancelFacilityBookingPreviewRequest $request)
    {
        $cancel_booking =  $this->facilityBookingRepository->cancelFacilityBooking($facilityBooking, $request->all(), Auth::user());
        return response()->json($cancel_booking);
    }

    /**
     * cancel the specified resource in storage.
     *
     * @param FacilityBooking $facilityBooking facility booking
     * @param  CancelFacilityBookingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function cancel_data(FacilityBooking $facilityBooking, CancelFacilityBookingRequest $request)
    {
        $this->facilityBookingRepository->cancelFacilityBookingData($facilityBooking, $request->all(), Auth::user());
        $this->triggerFacilityUpdateEvent($facilityBooking->facility);
        return response()->json(['Booking cancelled successfully']);
    }

    /**
     * reinstate the specified resource in storage.
     *
     * @param FacilityBooking $facilityBooking facility booking
     * @param  ReinstateFacilityBooking  $request
     * @return \Illuminate\Http\Response
     */
    public function reinstateBooking(FacilityBooking $facilityBooking, ReinstateFacilityBooking $request)
    {
        $this->facilityBookingRepository->reinstateFacilityBookingData($facilityBooking, $request->all(), Auth::user());
        $this->triggerFacilityUpdateEvent($facilityBooking->facility);
        return response()->json(['Booking reinstated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteFacilityBookingRequest $request, FacilityBooking $facilityBooking)
    {
        $this->triggerFacilityUpdateEvent($facilityBooking->facility);
        $this->facilityBookingRepository->destroyFacilityBooking($facilityBooking, Auth::user());
        return response()->json(['Booking deleted successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  FacilityBookerNote $facilityBookerNote
     * @return \Illuminate\Http\Response
     */
    public function bookerNoteDelete(FacilityBookerNote $facilityBookerNote)
    {
        $this->facilityBookingRepository->deleteSchedulerNote($facilityBookerNote, Auth::user());
        return redirect()->route('facility-booking.index');
    }

    /**
     * Get Facility bookings available recurrence count
     *
     * @param FacilityBooking $facilityBooking facility booking
     */
    public function getAvailableRecurrenceCount(FacilityBooking $facilityBooking)
    {
        return response()->json(['availableRecurrenceCount' => $facilityBooking->facilityBookingRecurrence->availableFacilityBookings()->count()]);
    }
}
