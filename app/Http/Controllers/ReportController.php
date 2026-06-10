<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(): View
    {
        $stats = $this->reports->dashboardStats();
        return view('reports.index', compact('stats'));
    }

    public function daily(Request $request): View
    {
        $data = $this->reports->dailySummary($request->date);
        return view('reports.daily', compact('data'));
    }

    public function monthly(Request $request): View
    {
        $data = $this->reports->monthlySummary($request->month, $request->year);
        return view('reports.monthly', compact('data'));
    }

    public function pending(): View
    {
        $invoices = $this->reports->pendingInvoices();
        return view('reports.pending', compact('invoices'));
    }

    public function bestSeller(): View
    {
        $item = $this->reports->bestSellerProduct();
        return view('reports.best-seller', compact('item'));
    }

    public function earningsDaily(Request $request): View
    {
        $data = $this->reports->dailyEarnings($request->date);
        return view('reports.earnings-daily', compact('data'));
    }

    public function earningsMonthly(Request $request): View
    {
        $data = $this->reports->monthlyEarnings($request->month, $request->year);
        return view('reports.earnings-monthly', compact('data'));
    }
}
