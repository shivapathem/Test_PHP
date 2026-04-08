<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Requests\DeleteServiceRequest;
use App\Models\Facility\Facility;
use App\Models\Facility\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ServiceController extends Controller
{
    /**
     * @var ServiceRepository
     */
    protected $serviceRepository;

    /**
     * ServiceController constructor.
     * @param ServiceRepositoryInterface $serviceRepository form repo
     */
    public function __construct(ServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
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
            "pages.admin.service.serviceList",
            [
            'serviceList' => $this->serviceRepository->getAllService()
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
        return View::make("pages.admin.service.ServiceForm");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreServiceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServiceRequest $request)
    {
        $this->authorize('create', Facility::class);
        $this->serviceRepository->saveService($request->service, Auth::user());
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
    public function edit(Service $service)
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.service.ServiceForm", [
            'service' => $service
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        $this->authorize('create', Facility::class);
        $this->serviceRepository->updateService($service, $request->service, Auth::user());
        return response()->json(['Record updated successfully']);
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  Service $service
     * @return \Illuminate\Http\Response
     */
    public function delete(Service $service)
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.service.ServiceDelete", [
            'service' => $service
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteServiceRequest $request, Service $service)
    {
        $this->serviceRepository->destroyService($service, Auth::user());
        return response()->json(['Record deleted successfully']);
    }

    /**
     * Get services list
     */
    public function facilitySerivceList()
    {
        return response()->json($this->serviceRepository->facilitySerivceList());
    }
}
