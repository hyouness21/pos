<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function dailySummary(?string $date = null): array
    {
        $day = $date ? Carbon::parse($date) : Carbon::today();

        $invoices = Invoice::whereDate('created_at', $day)
            ->with('customer')
            ->latest()
            ->get();

        return [
            'date'         => $day,
            'invoices'     => $invoices,
            'total'        => $invoices->sum('total_amount'),
            'paid'         => $invoices->where('status', 'paid')->sum('total_amount'),
            'pending'      => $invoices->where('status', 'pending')->sum('total_amount'),
            'count'        => $invoices->count(),
        ];
    }

    public function monthlySummary(?int $month = null, ?int $year = null): array
    {
        $month = $month ?? now()->month;
        $year  = $year  ?? now()->year;

        $invoices = Invoice::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->with('customer')
            ->latest()
            ->get();

        return [
            'month'    => $month,
            'year'     => $year,
            'invoices' => $invoices,
            'total'    => $invoices->sum('total_amount'),
            'paid'     => $invoices->where('status', 'paid')->sum('total_amount'),
            'pending'  => $invoices->where('status', 'pending')->sum('total_amount'),
            'count'    => $invoices->count(),
        ];
    }

    public function pendingInvoices(): Collection
    {
        return Invoice::where('status', 'pending')
            ->with('customer')
            ->latest()
            ->get();
    }

    public function bestCustomer(): ?Customer
    {
        return Customer::withSum('invoices', 'total_amount')
            ->orderByDesc('invoices_sum_total_amount')
            ->first();
    }

    public function bestSellerProduct(): ?Item
    {
        return Item::withSum('invoiceItems', 'quantity')
            ->orderByDesc('invoice_items_sum_quantity')
            ->first();
    }

    public function dailyEarnings(?string $date = null): array
    {
        $day = $date ? Carbon::parse($date) : Carbon::today();

        $rows = $this->earningsQuery()
            ->whereDate('invoices.created_at', $day)
            ->get();

        $expenses = Expense::whereDate('date', $day)
            ->orderBy('type')
            ->get();

        return $this->buildEarningsResult($rows, ['date' => $day, 'expenses' => $expenses]);
    }

    public function monthlyEarnings(?int $month = null, ?int $year = null): array
    {
        $month = $month ?? now()->month;
        $year  = $year  ?? now()->year;

        $rows = $this->earningsQuery()
            ->whereMonth('invoices.created_at', $month)
            ->whereYear('invoices.created_at', $year)
            ->get();

        $expenses = Expense::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('type')
            ->get();

        return $this->buildEarningsResult($rows, ['month' => $month, 'year' => $year, 'expenses' => $expenses]);
    }

    private function earningsQuery()
    {
        // Pro-rate the invoice-level discount across items by share of invoice subtotal.
        // adjusted_revenue = subtotal * (1 - discount / invoice_subtotal)
        //                  = subtotal * total_amount / (total_amount + discount)
        // paid_ratio  = amount_paid / total_amount  (how much of the invoice was collected)
        // disc_ratio  = total_amount / (total_amount + discount)  (accounts for invoice discount)
        // revenue contribution per line = subtotal * disc_ratio * paid_ratio
        //                               = subtotal * amount_paid / (total_amount + discount)
        // cost contribution per line    = quantity * cost_price * paid_ratio
        return InvoiceItem::join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('items', 'items.id', '=', 'invoice_items.item_id')
            ->where('invoices.amount_paid', '>', 0)
            ->whereNotNull('items.cost_price')
            ->select(
                'items.id as item_id',
                'items.name as item_name',
                DB::raw('SUM(invoice_items.quantity) as qty_sold'),
                DB::raw('SUM(invoice_items.subtotal * invoices.amount_paid / NULLIF(invoices.total_amount + invoices.discount, 0)) as revenue'),
                DB::raw('SUM(invoice_items.quantity * items.cost_price * invoices.amount_paid / NULLIF(invoices.total_amount, 0)) as cost'),
                DB::raw('SUM(invoice_items.subtotal * invoices.amount_paid / NULLIF(invoices.total_amount + invoices.discount, 0) - invoice_items.quantity * items.cost_price * invoices.amount_paid / NULLIF(invoices.total_amount, 0)) as profit')
            )
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('profit');
    }

    private function buildEarningsResult(Collection $rows, array $extra): array
    {
        $revenue = $rows->sum('revenue');
        $cost    = $rows->sum('cost');
        $profit  = $rows->sum('profit');
        $margin  = $revenue > 0 ? round($profit / $revenue * 100, 1) : 0;

        $expenses      = $extra['expenses'] ?? collect();
        $expensesTotal = $expenses->sum('amount');
        $netProfit     = $profit - $expensesTotal;

        return array_merge($extra, [
            'rows'           => $rows,
            'revenue'        => $revenue,
            'cost'           => $cost,
            'profit'         => $profit,
            'margin'         => $margin,
            'expenses_total' => $expensesTotal,
            'net_profit'     => $netProfit,
        ]);
    }

    public function dashboardStats(): array
    {
        $todayEarnings = $this->dailyEarnings();

        return [
            'today_revenue'   => Invoice::whereDate('created_at', today())->sum('total_amount'),
            'today_invoices'  => Invoice::whereDate('created_at', today())->count(),
            'today_profit'    => $todayEarnings['profit'],
            'today_margin'    => $todayEarnings['margin'],
            'pending_debt'    => Invoice::where('status', 'pending')->sum(DB::raw('total_amount - amount_paid')),
            'pending_count'   => Invoice::where('status', 'pending')->count(),
            'low_stock_count' => Item::whereColumn('stock', '<=', 'low_stock_threshold')->count(),
            'total_customers' => Customer::count(),
            'today_expenses'  => Expense::whereDate('date', today())->sum('amount'),
            'month_expenses'  => Expense::whereYear('date', now()->year)->whereMonth('date', now()->month)->sum('amount'),
        ];
    }
}
