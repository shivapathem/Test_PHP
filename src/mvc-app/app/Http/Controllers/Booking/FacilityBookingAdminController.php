<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\FacilityBookingAdminRepositoryInterface;
use App\Models\FacilityBooking\FacilityBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class FacilityBookingAdminController extends Controller
{
    /**
     * @var FacilityBookingAdminRepositoryInterface
     */
    protected $facilityBookingAdminRepository;

    /**
     * FacilityBookingController constructor.
     *
     * @param FacilityBookingAdminRepositoryInterface $facilityBookingAdminRepository facility booking repo
     */
    public function __construct(FacilityBookingAdminRepositoryInterface $facilityBookingAdminRepository)
    {
        $this->facilityBookingAdminRepository = $facilityBookingAdminRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return View::make(
            "pages.admin.facility-booking-administrator.facility-booking-admin"
        );
    }

    /**
     * Get tab data.
     *
     * @return View
     */
    public function getTabData(Request $request)
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(+3);
        $data = ['tabType' => $request->tabType];
        switch ($request->tabType) {
            case 'short_notice':
                $data['facilityBookingList'] = $this->facilityBookingAdminRepository->getAdministratorFacilityBookings(Auth::user(), $startDate, $endDate, FacilityBooking::BOOKING_STATUS_PENDING, 'Short Notice');
                break;
            case 'new':
                $data['facilityBookingListNewPrevious'] = $this->facilityBookingAdminRepository->getAdministratorFacilityBookings(Auth::user(), $startDate, $endDate, FacilityBooking::BOOKING_STATUS_NEW, 'New_Previous');
                $data['facilityBookingListNewFuture'] = $this->facilityBookingAdminRepository->getAdministratorFacilityBookings(Auth::user(), $startDate, $endDate, FacilityBooking::BOOKING_STATUS_NEW, 'New_Future');
                break;
            case 'pending':
                $data['facilityBookingListPendingPrevious'] = $this->facilityBookingAdminRepository->getAdministratorFacilityBookings(Auth::user(), $startDate, $endDate, FacilityBooking::BOOKING_STATUS_PENDING, 'Pending_Previous');
                $data['facilityBookingListPendingFuture'] = $this->facilityBookingAdminRepository->getAdministratorFacilityBookings(Auth::user(), $startDate, $endDate, FacilityBooking::BOOKING_STATUS_PENDING, 'Pending_Future');
                break;
            case 'declined':
                $data['facilityBookingListDeclined'] = $this->facilityBookingAdminRepository->getAdministratorFacilityBookings(Auth::user(), $startDate, $endDate, FacilityBooking::BOOKING_STATUS_DECLINED, 'Declined');
                break;
            case 'cancelled':
                $data['facilityBookingListCancelled'] = $this->facilityBookingAdminRepository->getAdministratorFacilityBookings(Auth::user(), $startDate, $endDate, FacilityBooking::BOOKING_STATUS_CANCELLED, 'Cancelled');
                break;
            default:
                break;
        }
        return View::make(
            "pages.admin.facility-booking-administrator.facility-booking-admin-list",
            $data
        );
    }
}
