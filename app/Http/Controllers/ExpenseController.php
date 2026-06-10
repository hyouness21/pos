<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();
        $month = now()->format('Y-m');

        $filterDate  = $request->input('date', $today);
        $filterMonth = $request->input('month', $month);

        $expenses = Expense::query()
            ->when($request->filled('date'),
                fn($q) => $q->whereDate('date', $filterDate))
            ->when(!$request->filled('date') && $request->filled('month'),
                fn($q) => $q->whereYear('date', substr($filterMonth, 0, 4))
                             ->whereMonth('date', substr($filterMonth, 5, 2)))
            ->when(!$request->filled('date') && !$request->filled('month'),
                fn($q) => $q->whereDate('date', $today))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $todayTotal = Expense::whereDate('date', $today)->sum('amount');
        $monthTotal = Expense::whereYear('date', now()->year)
                             ->whereMonth('date', now()->month)
                             ->sum('amount');

        $types = Expense::$types;

        return view('expenses.index', compact('expenses', 'todayTotal', 'monthTotal', 'types', 'filterDate', 'filterMonth'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'type'   => 'required|in:fuel,salary,item_cost,other',
            'amount' => 'required|numeric|min:0.01',
            'notes'  => 'nullable|string|max:500',
            'date'   => 'required|date',
        ]);

        Expense::create($request->only(['type', 'amount', 'notes', 'date']));

        return back()->with('success', 'Expense recorded.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();
        return back()->with('success', 'Expense deleted.');
    }
}
