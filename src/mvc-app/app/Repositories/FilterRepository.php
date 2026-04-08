<?php

namespace App\Repositories;

use App\Models\Filter\Filter;
use App\Models\User;
use App\Repositories\Contracts\FilterRepositoryInterface;
use Illuminate\Support\Collection;

class FilterRepository implements FilterRepositoryInterface
{
    /**
     * Save filter
     *
     * @param string $filterType filter type
     * @param string $filterPrivacyType filter privacy type
     * @param string $filterName filter name
     * @param array $filterData filter data
     * @param User   $user user creating record
     * @return
     */
    public function saveFilter(string $filterType, string $filterPrivacyType, string $filterName, array $filterData, User $user, $filterId): Filter
    {
        $whereConditions = [
            'FLR_FilterName' => $filterName,
            'FLR_FilterType' => $filterType,
            'FLR_FilterPrivacyType' => $filterPrivacyType,
        ];

        if ($filterPrivacyType === Filter::FILTER_PRIVACY_TYPE_PRIVATE) {
            $whereConditions['FLR_CreatedBy'] = $user->UD_UserID;
        }

        return Filter::updateOrCreate($whereConditions, [
            'FLR_FilterData_JSON' => json_encode($filterData, true),
            'FLR_CreatedBy' => $user->UD_UserID,
            'FLR_UpdatedBy' => $user->UD_UserID,
        ]);
    }

    /**
     * List filter
     *
     * @param string $filterType filter type
     * @param string $filterPrivacyType filter privacy type
     * @param User   $user user
     * @return
     */
    public function listFilter(string $filterType, string $filterPrivacyType, User $user): Collection
    {
        $filters = Filter::where('FLR_FilterType', $filterType)->where('FLR_FilterPrivacyType', $filterPrivacyType)->orderBy('FLR_FilterName');
        if ($filterPrivacyType == Filter::FILTER_PRIVACY_TYPE_PRIVATE) {
            $filters->where('FLR_CreatedBy', $user->UD_UserID);
        }
        return $filters->get();
    }

    /**
     * Delete filter
     *
     * @param Filter $filter delete filter
     * @return
     */
    public function deleteFilter(Filter $filter)
    {
        $filter->delete();
    }
}
