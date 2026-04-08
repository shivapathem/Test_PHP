<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFacilityTypeRequest;
use App\Http\Requests\UpdateFacilityTypeRequest;
use App\Http\Requests\DeleteFacilityTypeRequest;
use App\Models\Facility\Facility;
use App\Models\Facility\FacilityType;
use App\Repositories\Contracts\FacilityTypeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class FacilityTypeController extends Controller
{
    /**
     * @var FacilityTypeRepository
     */
    protected $facilityTypeRepository;

    /**
     * FacilityTypeController constructor.
     * @param FacilityTypeRepositoryInterface $facilityTypeRepository form repo
     */
    public function __construct(FacilityTypeRepositoryInterface $facilityTypeRepository)
    {
        $this->facilityTypeRepository = $facilityTypeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('create', Facility::class);
        return View::make(
            "pages.admin.facility-type.facilityTypeList",
            [
                'facilityTypeList' => $this->facilityTypeRepository->getAllFacilityType()
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.facility-type.facilityTypeForm");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreFacilityTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFacilityTypeRequest $request)
    {
        $this->authorize('create', Facility::class);
        $this->facilityTypeRepository->saveFacilityType($request->facility_type, $request->facility_sub_types, Auth::user());
        return response()->json(['Record added successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(FacilityType $facility_type)
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.facility-type.facilityTypeForm", [
            'facility_type' => $facility_type
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFacilityTypeRequest $request, FacilityType $facilityType)
    {
        $this->authorize('create', Facility::class);
        $this->facilityTypeRepository->updateFacilityType($facilityType, $request->facility_type, $request->facility_sub_types, Auth::user());
        return response()->json(['Record updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Service $service
     * @return \Illuminate\Http\Response
     */
    public function delete(FacilityType $facility_type)
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.facility-type.facilityTypeDelete", [
            'facility_type' => $facility_type
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteFacilityTypeRequest $request, FacilityType $facilityType)
    {
        $this->authorize('create', Facility::class);
        $this->facilityTypeRepository->destroyFacilitiyType($facilityType, Auth::user());
        return response()->json(['Record updated successfully']);
    }

    /**
     * Get list of facility Type
     */
    public function facilityTypeList()
    {
        return response()->json($this->facilityTypeRepository->facilityTypeList());
    }

    /**
     * Get list of facility Sub Type
     */
    public function facilitySubTypeList(Request $request)
    {
        return response()->json($this->facilityTypeRepository->facilitySubTypeList($request->facilityTypeIds));
    }
}
