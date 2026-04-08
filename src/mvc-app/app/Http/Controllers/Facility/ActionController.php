<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActionRequest;
use App\Http\Requests\UpdateActionRequest;
use App\Models\Facility\Action;
use App\Models\Facility\Facility;
use App\Repositories\Contracts\ActionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ActionController extends Controller
{
    /**
     * @var ActionRepositoryInterface
     */
    protected $actionRepository;

    /**
     * ActionController constructor.
     * @param ActionRepositoryInterface $actionRepository
     */
    public function __construct(ActionRepositoryInterface $actionRepository)
    {
        $this->actionRepository = $actionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.actions.actionList", [
            'actions' => $this->actionRepository->getAllActions()
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
        return View::make("pages.admin.actions.actionForm");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreActionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreActionRequest $request)
    {
        $this->authorize('create', Facility::class);
        $this->actionRepository->saveAction($request->validated(), Auth::user());
        return response()->json(['Record added successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  Action  $action
     * @return \Illuminate\Http\Response
     */
    public function show(Action $action)
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.actions.actionView", [
            'action' => $action
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Action  $action
     * @return \Illuminate\Http\Response
     */
    public function edit(Action $action)
    {
        $this->authorize('create', Facility::class);
        return View::make("pages.admin.actions.actionForm", [
            'action' => $action
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateActionRequest  $request
     * @param  Action  $action
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateActionRequest $request, Action $action)
    {
        $this->authorize('create', Facility::class);
        $this->actionRepository->updateAction($action, $request->validated(), Auth::user());
        return response()->json(['Record updated successfully']);
    }

    /**
     * Show the form for deleting the specified resource.
     *
     * @param  Action  $action
     * @return \Illuminate\Http\Response
     */
    public function delete(Action $action)
    {
        $this->authorize('create', Facility::class);
        $futureBookingsCount = $this->actionRepository->getFutureBookingsCount($action);
        return View::make("pages.admin.actions.actionDelete", [
            'action' => $action,
            'futureBookingsCount' => $futureBookingsCount
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Action  $action
     * @return \Illuminate\Http\Response
     */
    public function destroy(Action $action)
    {
        $this->authorize('create', Facility::class);
        $this->actionRepository->removeActionFromFutureBookings($action);
        $this->actionRepository->destroyAction($action, Auth::user());
        return response()->json(['Record deleted successfully']);
    }

    /**
     * Get actions list for API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActionsList()
    {
        return response()->json($this->actionRepository->getActionsList());
    }
}
