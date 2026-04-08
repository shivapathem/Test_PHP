<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Http\Requests\DeleteEquipmentRequest;
use App\Models\Facility\Equipment;
use App\Models\Facility\Facility;
use App\Repositories\Contracts\EquipmentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\JsonResponse;

class EquipmentController extends Controller
{
    /**
     * @var EquipmentRepository
     */
    protected $equipmentRepository;

    /**
     * EquipmentController constructor.
     * @param EquipmentRepositoryInterface $equipmentRepository form repo
     */
    public function __construct(EquipmentRepositoryInterface $equipmentRepository)
    {
        $this->equipmentRepository = $equipmentRepository;
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
            "pages.admin.equipment.equipmentList",
            [
            'equipmentList' => $this->equipmentRepository->getAllEquipment()
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
        return View::make(
            "pages.admin.equipment.EquipmentForm",
            [
                'equipmentTypeList' => Equipment::EquipemntTypeList()
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreEquipmentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        $this->authorize('create', Facility::class);
        $this->equipmentRepository->saveEquipment($request->all(), Auth::user());
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
    public function edit(Equipment $equipment)
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.equipment.equipmentForm", [
            'equipment' => $equipment,
            'equipmentTypeList' => Equipment::EquipemntTypeList()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment)
    {
        $this->authorize('create', Facility::class);
        $this->equipmentRepository->updateEquipment($equipment, $request->all(), Auth::user());
        return response()->json(['Record updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Equipment $equipment
     * @return \Illuminate\Http\Response
     */
    public function delete(Equipment $equipment)
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.equipment.EquipmentDelete", [
            'equipment' => $equipment
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteEquipmentRequest $request, Equipment $equipment)
    {
        $this->authorize('create', Facility::class);
        $this->equipmentRepository->destroyEquipment($equipment, Auth::user());
        return response()->json(['Record updated successfully']);
    }

    /**
     * Equipment List
     */
    public function facilityEquipmentList()
    {
        return response()->json($this->equipmentRepository->equipmentList());
    }
}
