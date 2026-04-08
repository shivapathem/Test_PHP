<?php

use App\Http\Controllers\Admin\AllocateUserController;
use App\Http\Controllers\Admin\AreaPermissionController;
use App\Http\Controllers\Facility\ActionController;
use App\Http\Controllers\Booking\FacilityBookingViewController;
use App\Http\Controllers\Booking\FacilityBookingApiController;
use App\Http\Controllers\Booking\FacilityBookingAdminController;
use App\Http\Controllers\Facility\EquipmentController;
use App\Http\Controllers\Facility\FacilityController;
use App\Http\Controllers\Facility\FacilityTypeController;
use App\Http\Controllers\Facility\LocationController;
use App\Http\Controllers\Facility\ServiceController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Filter\FilterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Scheduling\SchedulingGroupController;
use App\Http\Controllers\Setup\ScheduledPeopleController;
use App\Http\Controllers\Setup\ScheduleTeamHistoryController;
use App\Http\Controllers\Setup\StaffDetailsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('mvc-app')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::middleware('booking.menu.enabled')->group(function () {
            //Facility
            Route::resource('facility', FacilityController::class);
            Route::get('facility/restrict-bookers-list/{area}',  [FacilityController::class, 'getAreaTeam'])->name('facility.bookers');
            Route::get('facility/delete/{facility}', [FacilityController::class, 'delete'])->name('facility.deleteForm');
            Route::get('facility/archiveForm/{facility}', [FacilityController::class, 'archiveForm'])->name('facility.archiveForm');
            Route::get('facility/archive/{facility}', [FacilityController::class, 'archive'])->name('facility.archive');
            Route::get('facility/history/{facility}', [FacilityController::class, 'facilityHistory'])->name('facility.history');
            Route::get('facility/facility-administrator/{facility}', [FacilityController::class, 'facilityAdministrator'])->name('facility.facility-administrator');
            Route::post('facility/facility-administrator-store/{facility}', [FacilityController::class, 'facilityAdministratorStore'])->name('facility.facility-administrator-store');

            // Division
            Route::get('facility-area-list', [FacilityController::class, 'facilityAreaList'])->name('facilityAreaList');
            Route::get('facility/{facility}/future-booking-count', [FacilityController::class, 'countFutureBooking'])->name('facility.future-booking-count');

            //Facility booking
            Route::get('facility-booking', [FacilityBookingViewController::class, 'index'])->name('facility-booking.index');
            Route::post('facility/{facility}/facility-booking-request/create', [FacilityBookingViewController::class, 'create'])->name('facility-booking.create');
            Route::post('facility/{facility}/facility-booking-record/create', [FacilityBookingViewController::class, 'createRecord'])->name('facility-booking-record.create');
            Route::post('facility-booking/{facilityBooking}/edit', [FacilityBookingViewController::class, 'edit'])->name('facility-booking.edit');
            Route::post('facility-booking/{facilityBooking}/show', [FacilityBookingViewController::class, 'show'])->name('facility-booking.show');
            Route::post('facility/{facility}/facility-booking-request/store', [FacilityBookingApiController::class, 'store'])->name('facility-booking.store');
            Route::post('facility/{facility}/facility-booking-record/store', [FacilityBookingApiController::class, 'storeRecord'])->name('facility-booking-record.store');
            Route::post('facility-booking/{facilityBooking}/update', [FacilityBookingApiController::class, 'update'])->name('facility-booking.update');
            Route::post('facility-booking/{facilityBooking}/copy', [FacilityBookingViewController::class, 'copyFacilityBooking'])->name('facility-booking.copy');
            Route::post('facility-booking/{facilityBooking}/move-form', [FacilityBookingViewController::class, 'moveFacilityBooking'])->name('facility-booking.move-form');
            Route::post('facility/{facility}/facility-booking/{facilityBooking}/move', [FacilityBookingApiController::class, 'moveBooking'])->name('facility-booking.move');
            Route::post('facility-booking/{facilityBooking}/cancel', [FacilityBookingApiController::class, 'cancel'])->name('facility-booking.cancel');
            Route::post('facility-booking/{facilityBooking}/cancel_popup', [FacilityBookingViewController::class, 'cancel_popup'])->name('facility-booking.cancel_popup');
            Route::post('facility-booking/{facilityBooking}/cancel_data', [FacilityBookingApiController::class, 'cancel_data'])->name('facility-booking.cancel_data');
            Route::post('facility-booking/{facilityBooking}/reinstate-popup', [FacilityBookingViewController::class, 'reinstatePopup'])->name('facility-booking.reinstate_popup');
            Route::post('facility-booking/{facilityBooking}/reinstate-data', [FacilityBookingApiController::class, 'reinstateBooking'])->name('facility-booking.reinstate_data');
            Route::post('facility-booking/{facilityBooking}/history', [FacilityBookingViewController::class, 'history'])->name('facility-booking.history');
            Route::get('get-facility-bookings', [FacilityBookingApiController::class, 'getFacilityBookings'])->name('facilityBooking.getFacilityBookings');
            Route::post('facility-booking/{facilityBooking}/delete', [FacilityBookingApiController::class, 'destroy'])->name('facility-booking.delete');
            Route::get('facility-booking/{facilityBooking}/destroy', [FacilityBookingApiController::class, 'destroy'])->name('facility-booking.destroy');
            Route::get('facility-booking/{facilityBooking}/get-available-recurrence-count', [FacilityBookingApiController::class, 'getAvailableRecurrenceCount'])->name('facilityBooking.getAvailableRecurrenceCount');

            //Facility booker note
            Route::get('facility/{facility}/create-booker-note', [FacilityBookingViewController::class, 'createBookerNote'])->name('facility-booker-note.create');
            Route::post('facility/{facility}/store-booker-note', [FacilityBookingApiController::class, 'storeBookerNote'])->name('facility-booker-note.store');
            Route::get('facility-booker-note/{facilityBookerNote}/edit-booker-note', [FacilityBookingViewController::class, 'editBookerNote'])->name('facility-booker-note.edit');
            Route::post('facility-booker-note/{facilityBookerNote}/update-booker-note', [FacilityBookingApiController::class, 'updateBookerNote'])->name('facility-booker-note.update');
            Route::post('facility-booker-note/{facilityBookerNote}/history', [FacilityBookingViewController::class, 'bookerNotehistory'])->name('facility-booker-note.history');
            Route::post('facility-booker-note/{facilityBookerNote}/delete', [FacilityBookingApiController::class, 'bookerNoteDelete'])->name('facility-booker-note.delete');

            //Facility booking admin
            Route::get('facility-booking-admin', [FacilityBookingAdminController::class, 'index'])->name('facility-booking-admin.index');
            Route::post('facility-booking-admin/tab-data', [FacilityBookingAdminController::class, 'getTabData'])->name('facility-booking-admin.tab-data');

            //ExternalCustomer
            Route::resource('customer', CustomerController::class)->except(['show']);
            Route::get('customer-list', [CustomerController::class, 'customerList'])->name('customerList');
            Route::get('customer/delete/{customer}', [CustomerController::class, 'delete'])->name('customer.deleteForm');


            Route::prefix('admin')->group(function () {
                //Facility type
                Route::resource('facility-type', FacilityTypeController::class);
                Route::get('facility-type-list', [FacilityTypeController::class, 'facilityTypeList'])->name('facilityTypeList');
                Route::get('facility-sub-type-list', [FacilityTypeController::class, 'facilitySubTypeList'])->name('facilitySubTypeList');
                Route::get('facilitytype/delete/{facilitytype}', [FacilityTypeController::class, 'delete'])->name('facility-type.deleteForm');

                //Location
                Route::resource('location', LocationController::class);
                Route::get('location/delete/{location}', [LocationController::class, 'delete'])->name('location.deleteForm');

                //Service
                Route::resource('service', ServiceController::class);
                Route::get('facility-service-list', [ServiceController::class, 'facilitySerivceList'])->name('facilityServiceList');
                Route::get('service/delete/{service}', [ServiceController::class, 'delete'])->name('service.deleteForm');

                //Equipment
                Route::resource('equipment', EquipmentController::class);
                Route::get('equipment/delete/{equipment}', [EquipmentController::class, 'delete'])->name('equipment.deleteForm');
                Route::get('facility-equipment-list', [EquipmentController::class, 'facilityEquipmentList'])->name('facilityEquipmentList');

                //Actions
                Route::resource('action', ActionController::class);
                Route::get('action/delete/{action}', [ActionController::class, 'delete'])->name('action.deleteForm');
                Route::get('action-list', [ActionController::class, 'getActionsList'])->name('action.list');

                //Scheduling Group
                Route::get('scheduling-group', [SchedulingGroupController::class, 'index'])->name('scheduling-group.index');
                Route::get('scheduling-group/create', [SchedulingGroupController::class, 'create'])->name('scheduling-group.create');
                Route::post('scheduling-group', [SchedulingGroupController::class, 'store'])->name('scheduling-group.store');
                Route::get('scheduling-group/{schedulingGroup}', [SchedulingGroupController::class, 'show'])->name('scheduling-group.show');
                Route::get('scheduling-group/{schedulingGroup}/edit', [SchedulingGroupController::class, 'edit'])->name('scheduling-group.edit');
                Route::post('scheduling-group/{schedulingGroup}', [SchedulingGroupController::class, 'update'])->name('scheduling-group.update');
                Route::get('scheduling-group/delete/{schedulingGroup}', [SchedulingGroupController::class, 'delete'])->name('scheduling-group.deleteForm');
                Route::delete('scheduling-group-delete/{schedulingGroup}', [SchedulingGroupController::class, 'destroy'])->name('scheduling-group.destroy');
                Route::get('scheduling-group/area-teams/{area}', [SchedulingGroupController::class, 'getAreaTeams'])->name('scheduling-group.area-teams');
                Route::get('scheduling-group/history/{schedulingGroup}', [SchedulingGroupController::class, 'history'])->name('scheduling-group.history');
            });

            //Filter
            Route::post('save-filter', [FilterController::class, 'saveFilter'])->name('save.filter');
            Route::get('list-saved-filter', [FilterController::class, 'listFilter'])->name('list.filter');
            Route::get('delete-saved-filter/{filter}', [FilterController::class, 'deleteFilter'])->name('delete.filter');
        });

        Route::prefix('admin')->group(function () {
            //Allocate users
            //Allocate user tab
            Route::get('allocate-users', [AllocateUserController::class, 'index'])->name('admin.allocate-users.index');
            Route::post('allocate-users-table', [AllocateUserController::class, 'allocateUserTable'])->name('admin.allocate-users.allocate-user-table');
            Route::get('allocate-users-data', [AllocateUserController::class, 'getAllocateUsersData'])->name('admin.allocate-users.allocate-users-data');
            Route::get('allocate-user-info/{user}', [AllocateUserController::class, 'getUserInfo'])->name('admin.allocate-users.user-info');
            Route::get('add-allocate-user-form', [AllocateUserController::class, 'addAllocateUserForm'])->name('admin.allocate-users.add-allocate-user-form');
            Route::post('create-allocate-user', [AllocateUserController::class, 'createAllocateUser'])->name('admin.allocate-users.create-allocate-user');
            Route::get('get-staff-details-autocomplete-list', [AllocateUserController::class, 'getStaffDetailsAutocompleteList'])->name('admin.allocate-users.get-staff-details-autocomplete-list');

            //Area permissions tab
            Route::post('area-permissions-table', [AreaPermissionController::class, 'areaPermissionsTable'])->name('admin.allocate-users.area-permissions-table');
            Route::post('get-area-users/{areaId}', [AreaPermissionController::class, 'getAreaUsers'])->name('admin.allocate-users.get-area-users');
            Route::get('area-user-history/{areaId}/{userRoleId}', [AreaPermissionController::class, 'getAreaUserPermissionHistory'])->name('admin.allocate-users.area-user-history');
            Route::get('add-area-allocate-user-form', [AreaPermissionController::class, 'addAreaAllocateUserForm'])->name('admin.allocate-users.add-area-allocate-user-form');
            Route::post('create-area-allocate-user', [AreaPermissionController::class, 'createAreaAllocateUser'])->name('admin.allocate-users.create-area-allocate-user');
            Route::get('get-user-details', [AreaPermissionController::class, 'getUserDetailsForAreaAllocation'])->name('admin.allocate-users.get-user-details');
            Route::post('remove-area-user', [AreaPermissionController::class, 'removeAreaUser'])->name('admin.allocate-users.remove-area-user');
            Route::post('update-area-user-role', [AreaPermissionController::class, 'updateAreaUserRole'])->name('admin.allocate-users.update-area-user-role');
            Route::post('toggle-area-additional-role', [AreaPermissionController::class, 'toggleAreaAdditionalRole'])->name('admin.allocate-users.toggle-area-additional-role');
            //Permission descriptions tab
            Route::post('permission-descriptions-table', [AreaPermissionController::class, 'permissionDescriptionsTable'])->name('admin.allocate-users.permission-descriptions-table');
            //Scheduled and Non Scheduled tab
            Route::post('get-staff-view', [AllocateUserController::class, 'getStaffView'])->name('admin.allocate-users.get-staff-view');
            Route::post('search-staff-team', [AllocateUserController::class, 'searchStaffTeam'])->name('admin.allocate-users.search-staff-team');
            Route::post('set-users-permissions', [AllocateUserController::class, 'setUsersPermissions'])->name('admin.allocate-users.set-users-permissions');
            Route::post('remove-staff', [AllocateUserController::class, 'removeStaff'])->name('admin.allocate-users.remove-staff');
            Route::post('add-non-scheduled-staff', [AllocateUserController::class, 'addNonScheduledStaff'])->name('admin.allocate-users.add-non-scheduled-staff');
            Route::get('add-non-scheduled-staff-form', [AllocateUserController::class, 'addNonScheduledStaffForm'])->name('admin.allocate-users.add-non-scheduled-staff-form');
            Route::post('set-default-team', [AllocateUserController::class, 'setDefaultTeam'])->name('admin.allocate-users.set-default-team');
            Route::get('get-staff-history', [AllocateUserController::class, 'getStaffHistory'])->name('admin.allocate-users.get-staff-history');
        });

        // Setup
        Route::prefix('setup')->group(function () {
            Route::prefix('scheduled-people')->group(function () {
                Route::get('/', [ScheduledPeopleController::class, 'index'])->name('setup.scheduled-people.index');
                Route::get('/create', [ScheduledPeopleController::class, 'create'])->name('setup.scheduled-people.create');
                Route::post('/', [ScheduledPeopleController::class, 'store'])->name('setup.scheduled-people.store');
                Route::get('/{id}', [ScheduledPeopleController::class, 'show'])->whereNumber('id')->name('setup.scheduled-people.show');
                Route::get('/{id}/edit', [ScheduledPeopleController::class, 'edit'])->whereNumber('id')->name('setup.scheduled-people.edit');
                Route::post('/{id}', [ScheduledPeopleController::class, 'update'])->whereNumber('id')->name('setup.scheduled-people.update');

                // JSON helpers
                Route::get('/team-list', [ScheduledPeopleController::class, 'teamList'])->name('setup.scheduled-people.team-list');
                Route::get('/{id}/details', [ScheduledPeopleController::class, 'details'])->name('setup.scheduled-people.details');
    
                // Validations / actions
                Route::post('/validate-additional-team', [ScheduledPeopleController::class, 'validateAdditionalTeam'])->name('setup.scheduled-people.validate-additional-team');

                Route::post('{id}/staff', [StaffDetailsController::class, 'updateStaff'])->whereNumber('id')->name('setup.scheduled-people.updateStaff');
                
                // Search
                Route::match(['get', 'post'], '/search', [ScheduledPeopleController::class, 'search'])->name('setup.scheduled-people.search');
                Route::get('/permissions', [ScheduledPeopleController::class, 'getPermissions'])->name('setup.scheduled-people.permissions');
                
                // Staff Details - Search (separate route for StaffDetailsController)
                Route::post('/staff-search', [StaffDetailsController::class, 'searchStaff'])->name('setup.scheduled-people.staff-search');

                // Staff Details - Attach, Detach, Show
                Route::post('/attach/{id}', [StaffDetailsController::class, 'attachStaff'])->whereNumber('id')->name('setup.scheduled-people.attach');
                Route::post('/detach/{id}', [StaffDetailsController::class, 'detachStaff'])->whereNumber('id')->name('setup.scheduled-people.detach');
                Route::get('/getStaff/{id}', [StaffDetailsController::class, 'getStaff'])->whereNumber('id')->name('setup.scheduled-people.getStaff');
                // Staff Details Autocomplete - at setup level
                Route::get('/staff-autocomplete', [StaffDetailsController::class, 'staffAutocomplete'])->name('setup.scheduled-people.staff-autocomplete');

                Route::get('/{id}/team-history', [ScheduleTeamHistoryController::class, 'teamHistory'])->name('setup.scheduled-people.team-history');

                // Contract History
                Route::get('/contract-history', [ScheduleTeamHistoryController::class, 'contractHistory'])->name('setup.scheduled-people.contract-history');
                Route::post('/contract-history-popup', [ScheduleTeamHistoryController::class, 'contractHistoryPopup'])->name('setup.scheduled-people.contract-history-popup');
                Route::post('/validate-rota', [ScheduleTeamHistoryController::class, 'validateRota'])->name('setup.scheduled-people.validate-rota');
                Route::post('/delete-home-team', [ScheduleTeamHistoryController::class, 'deleteHomeTeam'])->name('setup.scheduled-people.delete-home-team');
                Route::post('/conflicting-duties', [ScheduleTeamHistoryController::class, 'getConflictingDuties'])->name('setup.scheduled-people.conflicting-duties');
                Route::post('/remove-conflicting-duties', [ScheduleTeamHistoryController::class, 'removeConflictingDuties'])->name('setup.scheduled-people.remove-conflicting-duties');
                Route::get('/home-team/{homeTeamId}/has-rota', [ScheduleTeamHistoryController::class, 'homeTeamHasRota'])->name('setup.scheduled-people.home-team.has-rota');
            });
        });
    });
});
