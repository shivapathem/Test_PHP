<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteCustomerRequest;
use App\Models\FacilityBooking\ExternalCustomer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomerController extends Controller
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * CustomerController constructor.
     * @param CustomerRepositoryInterface $customerRepository
     */

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return View::make("pages.customer.customerList", [
            'customerList' => $this->customerRepository->getAllCustomer()
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return View::make("pages.customer.customerForm", [
            'customerList' => $this->customerRepository->getAllCustomer()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCustomerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $this->customerRepository->saveCustomer($request->all(), Auth::user());
        return response()->json(['Record added successfully']);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ExternalCustomer $customer)
    {
        return View::make("pages.customer.customerForm", [
            'customer' => $customer
        ]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomerRequest $request, ExternalCustomer $customer)
    {
        $this->customerRepository->updateCustomer($customer, $request->all(), Auth::user());
        return response()->json(['Record updated successfully']);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  ExternalCustomer $customer
     * @return \Illuminate\Http\Response
     */
    public function delete(ExternalCustomer $customer)
    {
        return View::make("pages.customer.CustomerDelete", [
            'customer' => $customer
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteCustomerRequest $request, ExternalCustomer $customer)
    {
        $delete_allowed = $this->customerRepository->destroyCustomer($customer, Auth::user());
        return  response()->json($delete_allowed);
    }
}
