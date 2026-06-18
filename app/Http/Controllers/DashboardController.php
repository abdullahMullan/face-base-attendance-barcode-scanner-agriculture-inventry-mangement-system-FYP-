<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Cache dashboard data for 5 minutes to reduce database hits
        $cacheKey = 'dashboard_stats_'.now()->format('YmdHi');
        
        $dashboardData = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            // Combine sales statistics into a single query
            $saleStats = Sale::selectRaw('
                COUNT(*) as total_count,
                SUM(total_amount) as total_amount,
                SUM(CASE WHEN payment_type = "cash" THEN total_amount ELSE 0 END) as cash_amount,
                SUM(CASE WHEN payment_type = "credit" THEN total_amount ELSE 0 END) as credit_amount,
                SUM(due_amount) as total_pending,
                SUM(paid_amount) as total_earnings
            ')->first();

            // Get profit by payment type
            $profitStats = Sale::join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
                ->selectRaw('
                    SUM(CASE WHEN sales.payment_type = "cash" THEN sale_items.profit_amount ELSE 0 END) as cash_profit,
                    SUM(CASE WHEN sales.payment_type = "credit" THEN sale_items.profit_amount ELSE 0 END) as credit_profit
                ')
                ->first();

            // Get product stats
            $productStats = Product::selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock
            ')->first();

            return [
                'saleStats' => $saleStats,
                'profitStats' => $profitStats,
                'productStats' => $productStats,
            ];
        });

        $totalProducts = (int) $dashboardData['productStats']->total_count;
        $totalCustomers = Cache::remember('total_customers', now()->addMinutes(30), fn () => Customer::count());
        $totalSalesAmount = (float) ($dashboardData['saleStats']->total_amount ?? 0);
        $totalCashSalesAmount = (float) ($dashboardData['saleStats']->cash_amount ?? 0);
        $totalCreditSalesAmount = (float) ($dashboardData['saleStats']->credit_amount ?? 0);
        $totalPending = (float) ($dashboardData['saleStats']->total_pending ?? 0);
        $totalEarnings = (float) ($dashboardData['saleStats']->total_earnings ?? 0);
        $cashProfit = (float) ($dashboardData['profitStats']->cash_profit ?? 0);
        $creditProfit = (float) ($dashboardData['profitStats']->credit_profit ?? 0);
        $outOfStockCount = (int) $dashboardData['productStats']->out_of_stock;

        // Low stock products - only select needed columns
        $lowStockProducts = Product::select('id', 'name', 'stock_quantity', 'low_stock_threshold')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();

        // Recent sales with eager loading
        $recentSales = Sale::with(['customer:id,name,phone', 'items:sale_id,product_id,quantity,profit_amount', 'items.product:id,name,selling_price'])
            ->select('id', 'customer_id', 'invoice_no', 'sale_date', 'total_amount', 'payment_type')
            ->latest('sale_date')
            ->limit(10)
            ->get();

        // Monthly trend - cached separately
        $monthlyTrend = Cache::remember('monthly_trend_'.now()->format('YmdHi'), now()->addMinutes(5), function () {
            return Sale::selectRaw('
                DATE_FORMAT(sale_date, "%Y-%m") as month_key,
                DATE_FORMAT(MIN(sale_date), "%b %Y") as label,
                SUM(total_amount) as total
            ')
            ->whereBetween('sale_date', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->groupByRaw('DATE_FORMAT(sale_date, "%Y-%m")')
            ->orderBy('month_key')
            ->get();
        });

        $chartLabels = $monthlyTrend->pluck('label');
        $chartTotals = $monthlyTrend->pluck('total')->map(fn ($value) => (float) $value);

        $paymentSplit = [
            (float) $totalCashSalesAmount,
            (float) $totalCreditSalesAmount,
        ];

        return view('dashboard', compact(
            'totalProducts',
            'totalCustomers',
            'totalSalesAmount',
            'totalCashSalesAmount',
            'totalCreditSalesAmount',
            'totalPending',
            'totalEarnings',
            'cashProfit',
            'creditProfit',
            'lowStockProducts',
            'outOfStockCount',
            'recentSales',
            'chartLabels',
            'chartTotals',
            'paymentSplit'
        ));
    }
}
