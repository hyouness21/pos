<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::withSum('invoices', 'total_amount')
            ->withCount('invoices')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->input('search') . '%'))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function best(ReportService $reports): View
    {
        $customer = $reports->bestCustomer();
        return view('customers.best', compact('customer'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50|unique:customers,phone',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created.');
    }

    public function show(Customer $customer): View
    {
        $invoices = $customer->invoices()->latest()->with('items.item')->paginate(15);
        return view('customers.show', compact('customer', 'invoices'));
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => "nullable|string|max:50|unique:customers,phone,{$customer->id}",
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $customer->update($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->invoices()->exists()) {
            return back()->withErrors(['delete' => 'This customer has invoices and cannot be deleted.']);
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }
}
