<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Http\Requests\DeleteLocationRequest;
use App\Models\Facility\Facility;
use App\Models\Facility\Location;
use App\Repositories\Contracts\LocationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class LocationController extends Controller
{
    /**
     * @var LocationRepository
     */
    protected $locationRepository;

    /**
     * LocationController constructor.
     * @param LocationRepositoryInterface $locationRepository form repo
     */
    public function __construct(LocationRepositoryInterface $locationRepository)
    {
        $this->locationRepository = $locationRepository;
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
            "pages.admin.location.locationList",
            [
                'locationList' => $this->locationRepository->getAllLocation()
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
        return View::make("pages.admin.location.locationForm");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreLocationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLocationRequest $request)
    {
        $this->authorize('create', Facility::class);
        $this->locationRepository->saveLocation($request->location, Auth::user());
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
     * @param  Location  $location
     * @return \Illuminate\Http\Response
     */
    public function edit(Location $location)
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.location.locationForm", [
            'location' => $location
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLocationRequest $request, Location $location)
    {
        $this->authorize('create', Facility::class);
        $this->locationRepository->updateLocation($location, $request->location, Auth::user());
        return response()->json(['Record updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Service $service
     * @return \Illuminate\Http\Response
     */
    public function delete(Location $location)
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.location.locationDelete", [
            'location' => $location
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteLocationRequest $request, Location $location)
    {
        $this->authorize('create', Facility::class);
        $this->locationRepository->destroyLocation($location, Auth::user());
        return response()->json(['Record updated successfully']);
    }
}
