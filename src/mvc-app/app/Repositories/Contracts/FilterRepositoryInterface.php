<?php
namespace App\Repositories\Contracts;

use App\Models\Facility\Facility;
use App\Models\Filter\Filter;
use App\Models\User;
use Illuminate\Support\Collection;

interface FilterRepositoryInterface {
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
    public function saveFilter(string $filterType, string $filterPrivacyType, string $filterName, array $filterData, User $user, $filterId): Filter;

    /**
     * List filter
     *
     * @param string $filterType filter type
     * @param string $filterPrivacyType filter privacy type
     * @param User   $user user
     * @return
     */
    public function listFilter(string $filterType, string $filterPrivacyType, User $user): Collection;

    /**
     * Delete filter
     *
     * @param Filter $filter delete filter
     * @return
     */
    public function deleteFilter(Filter $filter);
}