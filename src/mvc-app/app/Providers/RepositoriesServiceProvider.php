<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * RepositoriesServiceProvider
 *
 * Service provider to enable us to bind an repository interfaces to given
 * implementations and thereby inject the implementation where dependencies
 * are injected by the service container via type-hint e.g. controller classes
 * (we would type hint the interface NOT the implentation)
 *
 */
class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(
            \App\Repositories\Contracts\FacilityTypeRepositoryInterface::class,
            \App\Repositories\FacilityTypeRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\LocationRepositoryInterface::class,
            \App\Repositories\LocationRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\FacilityRepositoryInterface::class,
            \App\Repositories\FacilityRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\ServiceRepositoryInterface::class,
            \App\Repositories\ServiceRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\EquipmentRepositoryInterface::class,
            \App\Repositories\EquipmentRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\FilterRepositoryInterface::class,
            \App\Repositories\FilterRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\FacilityBookingRepositoryInterface::class,
            \App\Repositories\FacilityBookingRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\ActionRepositoryInterface::class,
            \App\Repositories\ActionRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\CustomerRepositoryInterface::class,
            \App\Repositories\CustomerRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\FacilityBookingAdminRepositoryInterface::class,
            \App\Repositories\FacilityBookingAdminRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\SchedulingGroupRepositoryInterface::class,
            \App\Repositories\SchedulingGroupRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\Admin\AllocateUserRepositoryInterface::class,
            \App\Repositories\Admin\AllocateUserRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\Admin\ScheduledPeopleRepositoryInterface::class,
            \App\Repositories\Admin\ScheduledPeopleRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\Admin\CreateScheduledPersonRepositoryInterface::class,
            \App\Repositories\Admin\CreateScheduledPersonRepository::class
        );
    }
}
