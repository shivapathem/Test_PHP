<?php

namespace App\Http\Controllers\Filter;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFilterRequest;
use App\Models\Filter\Filter;
use App\Repositories\Contracts\FilterRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    /**
     * @var FilterRepository
     */
    protected $filterRepository;

    /**
     * FacilityController constructor.
     *
     * @param FilterRepositoryInterface $filterRepository filter repo
     */
    public function __construct(FilterRepositoryInterface $filterRepository)
    {
        $this->filterRepository = $filterRepository;
    }

    /**
     * Save filter
     * @param StoreFilterRequest $request request
     */
    public function saveFilter(StoreFilterRequest $request)
    {
        $filterResMsg = "Filter added successfully";
        $filter =  $this->filterRepository->saveFilter($request->filterType, $request->filterPrivacyType, $request->filterName, $request->filterData, Auth::user(), $request->filterId);
        if (!$filter->wasRecentlyCreated) {
            $filterResMsg = "Filter updated successfully";
        }
        return response()->json(['message' => $filterResMsg, 'filterId' => $filter->FLR_FilterID]);
    }

    /**
     * List filter
     * @param Request $request request
     */
    public function listFilter(Request $request)
    {
        return response()->json(
            $this->filterRepository->listFilter($request->filterType, $request->filterPrivacyType, Auth::user())->map(function ($filter) {
                return $filter->only(['FLR_FilterID', 'FLR_FilterName', 'FLR_FilterData_JSON']);
            })->toArray()
        );
    }

    /**
     * Delete filter
     * @param Filter $filter
     */
    public function deleteFilter(Filter $filter)
    {
        $this->filterRepository->deleteFilter($filter);
        return response()->json(['Filter deleted successfully']);
    }
}
