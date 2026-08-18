<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Quotation;
use App\Http\Requests\Web\StoreCustomerRequest;
use App\Http\Requests\Web\UpdateCustomerRequest;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerWebController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $customers = $query->orderBy('id', 'desc')->paginate(10);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $this->customerService->createCustomer($request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function storeAjax(StoreCustomerRequest $request)
    {
        $customer = $this->customerService->createCustomer($request->validated());
        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    public function show(Customer $customer)
    {
        $quotations = Quotation::where('customer_id', $customer->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('customers.show', compact('customer', 'quotations'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->customerService->updateCustomer($customer, $request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->customerService->deleteCustomer($customer);
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
