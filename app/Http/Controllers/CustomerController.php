<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $customers = Customer::query()
            ->when($request->search, fn ($q, $s) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->withCount(['vehicles', 'jobOrders'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): \Illuminate\View\View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'nullable|email|unique:customers',
            'phone'     => 'required|string|max:20',
            'phone_alt' => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'city'      => 'nullable|string|max:100',
            'notes'     => 'nullable|string',
        ]);

        $customer = Customer::create($data);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('app.customers.created'));
    }

    public function show(Customer $customer): \Illuminate\View\View
    {
        $customer->load([
            'vehicles',
            'jobOrders.vehicle',
            'jobOrders.staff.user',
            'invoices',
        ]);

        return view('customers.show', [
            'customer'      => $customer,
            'total_revenue' => $customer->total_revenue,
        ]);
    }

    public function edit(Customer $customer): \Illuminate\View\View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone'     => 'required|string|max:20',
            'phone_alt' => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'city'      => 'nullable|string|max:100',
            'status'    => 'in:active,inactive',
            'notes'     => 'nullable|string',
        ]);

        $customer->update($data);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('app.customers.updated'));
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', __('app.customers.deleted'));
    }
}
