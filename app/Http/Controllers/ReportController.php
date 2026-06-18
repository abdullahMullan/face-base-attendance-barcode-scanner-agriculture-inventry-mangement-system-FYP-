<?php

namespace App\Http\Controllers;

use App\Exports\ReportsWorkbookExport;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from', now()->startOfMonth());
        $to = $request->date('to', now()->endOfMonth());

        $salesQuery = Sale::with(['customer', 'user', 'items.product'])
            ->whereBetween('sale_date', [$from, $to]);

        $sales = (clone $salesQuery)->latest()->paginate(20);

        $summary = [
            'total_sales' => (float) (clone $salesQuery)->sum('total_amount'),
            'cash_sales' => (float) (clone $salesQuery)->where('payment_type', 'cash')->sum('total_amount'),
            'credit_sales' => (float) (clone $salesQuery)->where('payment_type', 'credit')->sum('total_amount'),
            'total_paid' => (float) (clone $salesQuery)->sum('paid_amount'),
            'total_pending' => (float) (clone $salesQuery)->sum('due_amount'),
            'credit_customers' => (int) Customer::whereHas('sales', function ($query) use ($from, $to): void {
                $query->where('payment_type', 'credit')->whereBetween('sale_date', [$from, $to]);
            })->count(),
        ];

        $monthlySales = Sale::selectRaw('DATE_FORMAT(sale_date, "%Y-%m") as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $customerBalances = Customer::withSum(['sales as due_total' => function ($query): void {
            $query->where('payment_type', 'credit');
        }], 'due_amount')
            ->orderByDesc('due_total')
            ->limit(20)
            ->get();

        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->get();

        return view('reports.index', compact('sales', 'summary', 'monthlySales', 'customerBalances', 'lowStockProducts', 'from', 'to'));
    }

    public function exportSalesCsv(Request $request): StreamedResponse
    {
        $from = $request->date('from', now()->startOfMonth());
        $to = $request->date('to', now()->endOfMonth());

        $sales = Sale::with(['customer', 'user'])
            ->whereBetween('sale_date', [$from, $to])
            ->orderBy('sale_date')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv"',
        ];

        $callback = function () use ($sales): void {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Invoice', 'Date', 'Customer', 'Payment Type', 'Total', 'Paid', 'Due', 'Status', 'Created By']);

            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->invoice_no,
                    $sale->sale_date?->format('Y-m-d'),
                    $sale->customer?->name ?? 'Walk-in',
                    strtoupper($sale->payment_type),
                    (float) $sale->total_amount,
                    (float) $sale->paid_amount,
                    (float) $sale->due_amount,
                    strtoupper($sale->payment_status),
                    $sale->user?->name,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportCreditCustomersCsv(): StreamedResponse
    {
        $customers = Customer::withSum(['sales as due_total' => function ($query): void {
            $query->where('payment_type', 'credit');
        }], 'due_amount')
            ->having('due_total', '>', 0)
            ->orderByDesc('due_total')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="credit-customers-report.csv"',
        ];

        $callback = function () use ($customers): void {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Customer Name', 'Phone', 'Email', 'Address', 'Pending Due']);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->name,
                    $customer->phone,
                    $customer->email,
                    $customer->address,
                    (float) ($customer->due_total ?? 0),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $from = $request->date('from', now()->startOfMonth());
        $to = $request->date('to', now()->endOfMonth());

        $filename = 'reports-'.$from->format('Ymd').'-'.$to->format('Ymd').'.xlsx';

        return Excel::download(new ReportsWorkbookExport($from, $to), $filename);
    }
}
