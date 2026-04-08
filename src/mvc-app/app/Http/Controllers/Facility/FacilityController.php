<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Models\Facility\Equipment;
use App\Models\Facility\Facility;
use App\Models\Facility\FacilityLocation;
use App\Models\Facility\FacilityType;
use App\Models\Facility\Location;
use App\Models\Facility\Service;
use App\Models\Scheduling\Division;
use App\Repositories\Contracts\FacilityRepositoryInterface;
use Illuminate\Support\Facades\View;
use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use App\Http\Requests\ArchiveFacilityRequest;
use App\Trait\WebSocketEventTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    use WebSocketEventTrait;

    /**
     * @var FacilityRepository
     */
    protected $facilityRepository;

    /**
     * FacilityController constructor.
     *
     * @param FacilityRepositoryInterface $facilityRepository facility repo
     */
    public function __construct(FacilityRepositoryInterface $facilityRepository)
    {
        $this->facilityRepository = $facilityRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return View::make("pages.facility.facility", [
            'facilityList' => $this->facilityRepository->getFacilityList()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', Facility::class);
        return View::make(
            "pages.facility.form.facilityForm",
            [
                'areaList' => $this->facilityRepository->getAreaList(Auth::user()),
                'providerTypeList' => FacilityLocation::providerTypeList(),
                'internalLocationList' => Location::all(),
                'facilityTypeList' => FacilityType::with('facilitySubType')->get(),
                'serviceList' => Service::all(),
                'equipmentList' => Equipment::all(),
                'defaultBookingList' => Facility::defaultBookingList(),
                'bookingPrivateList' => Facility::bookingPrivateList(),
                'accessibleList' => Facility::accessibleList(),
                'facilityList' => Facility::all(),
                'viewOnly' => false,
                'bookingsExist' => false
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFacilityRequest $request)
    {
        $this->authorize('view-any', Facility::class);
        $this->facilityRepository->saveFacility(Auth::user(), $request->all());
        return  response()->json(['Record added successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  Facility $facility
     * @return \Illuminate\Http\Response
     */
    public function show(Facility $facility)
    {
        return View::make(
            "pages.facility.form.facilityViewForm",
            [
                'areaList' => $this->facilityRepository->getAreaList(),
                'providerTypeList' => FacilityLocation::providerTypeList(),
                'internalLocationList' => Location::all(),
                'facilityTypeList' => FacilityType::with('facilitySubType')->get(),
                'serviceList' => Service::all(),
                'equipmentList' => Equipment::all(),
                'defaultBookingList' => Facility::defaultBookingList(),
                'bookingPrivateList' => Facility::bookingPrivateList(),
                'accessibleList' => Facility::accessibleList(),
                'facilityList' => Facility::all(),
                'editFacility' => $facility->load(['facilityAreaOwner.schedulingTeam', 'facilityRestrictBookers', 'facilitySubTypes']),
                'viewOnly' => true,
                'bookingsExist' => false
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function edit(Facility $facility)
    {
        $this->authorize('update', $facility);
        return View::make(
            "pages.facility.form.facilityForm",
            [
                'areaList' => $this->facilityRepository->getAreaList(Auth::user()),
                'providerTypeList' => FacilityLocation::providerTypeList(),
                'internalLocationList' => Location::all(),
                'facilityTypeList' => FacilityType::with('facilitySubType')->get(),
                'serviceList' => Service::all(),
                'equipmentList' => Equipment::all(),
                'defaultBookingList' => Facility::defaultBookingList(),
                'bookingPrivateList' => Facility::bookingPrivateList(),
                'accessibleList' => Facility::accessibleList(),
                'facilityList' => Facility::with('belongsToLinkedFacilitiesAsMandatory')->get(),
                'editFacility' => $facility->load(['facilityAreaOwner.schedulingTeam', 'facilityRestrictBookers', 'facilitySubTypes']),
                'viewOnly' => false,
                'bookingsExist' => $facility->facilityBookings()->count() > 0 ? true : false
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateFacilityRequest $request
     * @param  Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFacilityRequest $request, Facility $facility)
    {
        $this->authorize('update', $facility);
        $this->facilityRepository->saveFacility(Auth::user(), $request->all(), $facility);
         $this->triggerFacilityUpdateEvent($facility);
        return  response()->json(['Record updated successfully']);
    }

    /**
     * Get list of teams associated to the area
     *
     * @param  Division  $area
     * @return \Illuminate\Http\Response
     */
    public function getAreaTeam(Division $area)
    {
        return response()->json(
            $this->facilityRepository->getAreaTeamList($area)->pluck('schedulingTeamName', 'schedulingTeamId')
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Facility $facility
     * @return \Illuminate\Http\Response
     */
    public function delete(Facility $facility)
    {
        $this->authorize('view-any', Facility::class);
        return View::make("pages.facility.form.facilityDelete", [
            'facility' => $facility
        ]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Facility $facility)
    {
        $this->authorize('view-any', Facility::class);
        $delete_allowed = $this->facilityRepository->destroyFacility($facility, Auth::user());
        return  response()->json($delete_allowed);
    }

    /**
     * Archive facility view form.
     *
     * @param  Facility $facility
     * @return \Illuminate\Http\Response
     */
    public function archiveForm(Facility $facility)
    {
        $this->authorize('view-any', Facility::class);
        return View::make("pages.facility.form.facilityArchive", [
            'facility' => $facility
        ]);
    }

    /**
     * Archive facility store data.
     *
     * @param  Facility $facility
     * @return \Illuminate\Http\Response
     */
    public function archive(ArchiveFacilityRequest $request, Facility $facility)
    {
        $this->authorize('view-any', Facility::class);
        $archive_allowed = $this->facilityRepository->archiveFacility($request->all(), $facility, Auth::user());
        return  response()->json($archive_allowed);
    }

    /**
     * Get facility history.
     *
     * @param  Facility $facility
     * @return \Illuminate\Http\Response
     */
    public function facilityHistory(Facility $facility)
    {
        return View::make("pages.history.history-view", [
            'title' => __('Facility :facility History', ['facility' => $facility->FC_FacilityName]),
            'historyLogs' => $facility->history()->orderBy('HL_ID', 'DESC')->get()
        ]);
    }

    /**
     * Get list of areas
     */
    public function facilityAreaList()
    {
        return response()->json($this->facilityRepository->facilityAreaList());
    }

      /**
     * Get booking count by after date with new and pending status
     */
    public function countFutureBooking(Facility $facility, Request $request)
    {
        $fromBookingStatusDay = (int) ($request['from'] ?? 0);
        $toBookingStatusDay   = (int) ($request['to'] ?? 365);
        $count = $this->facilityRepository->countFutureBookingsByStatus($facility, $fromBookingStatusDay, $toBookingStatusDay);
        return response()->json(['future_booking_count' => $count]);
    }

    /**
     * Display the list of facility administrator.
     *
     * @param  Facility $facility
     * @return \Illuminate\Http\Response
     */
    public function facilityAdministrator(Facility $facility)
    {
        $this->authorize('facilityAdminReal', $facility);
        return View::make(
            "pages.facility.form.facility-administrator",
            [
                'facility' => $facility
            ]
        );
    }

    /**
     * Display the list of facility administrator.
     *
     * @param  Facility $facility
     * @return \Illuminate\Http\Response
     */
    public function facilityAdministratorStore(Facility $facility, Request $request)
    {
        $this->authorize('facilityAdminReal', $facility);
        $this->facilityRepository->facilityAdministratorStore($facility, $request->all(), Auth::user());
        return  response()->json(['Facility Administrators updated successfully']);
    }
}
