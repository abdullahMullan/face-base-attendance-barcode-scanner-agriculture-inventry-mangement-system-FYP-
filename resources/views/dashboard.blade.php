<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-grid-1x2-fill me-2"></i>Agriculture Dashboard</h2>
    </x-slot>

    <div class="page-wrap py-8 sm:py-10">
        <div>
            @if (session('success'))
                <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-3">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-3">{{ $errors->first() }}</div>
            @endif

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="panel card-fade-in panel-hover p-4"><p class="flex items-center gap-2 text-sm text-gray-500"><i class="bi bi-box-seam text-indigo-600"></i>Total Products</p><p class="text-2xl font-bold">{{ $totalProducts }}</p></div>
                <div class="panel card-fade-in panel-hover p-4"><p class="flex items-center gap-2 text-sm text-gray-500"><i class="bi bi-people text-indigo-600"></i>Total Customers</p><p class="text-2xl font-bold">{{ $totalCustomers }}</p></div>
                <div class="panel card-fade-in panel-hover p-4"><p class="flex items-center gap-2 text-sm text-gray-500"><i class="bi bi-receipt text-indigo-600"></i>Total Sales Amount</p><p class="text-2xl font-bold">Rs {{ number_format($totalSalesAmount, 2) }}</p></div>
                <div class="panel card-fade-in panel-hover p-4"><p class="flex items-center gap-2 text-sm text-gray-500"><i class="bi bi-cash-coin text-green-600"></i>Total Earnings (Received)</p><p class="text-2xl font-bold text-green-700">Rs {{ number_format($totalEarnings, 2) }}</p></div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="panel card-fade-in panel-hover p-4"><p class="flex items-center gap-2 text-sm text-gray-500"><i class="bi bi-cash-stack text-emerald-600"></i>Cash Sales</p><p class="text-xl font-semibold">Rs {{ number_format($totalCashSalesAmount, 2) }}</p></div>
                <div class="panel card-fade-in panel-hover p-4"><p class="flex items-center gap-2 text-sm text-gray-500"><i class="bi bi-wallet2 text-violet-600"></i>Credit Sales</p><p class="text-xl font-semibold">Rs {{ number_format($totalCreditSalesAmount, 2) }}</p></div>
                <div class="panel card-fade-in panel-hover p-4"><p class="flex items-center gap-2 text-sm text-gray-500"><i class="bi bi-graph-up-arrow text-blue-600"></i>Cash Profit</p><p class="text-xl font-semibold text-blue-700">Rs {{ number_format($cashProfit, 2) }}</p></div>
                <div class="panel card-fade-in panel-hover p-4"><p class="flex items-center gap-2 text-sm text-gray-500"><i class="bi bi-pie-chart text-purple-600"></i>Credit Profit</p><p class="text-xl font-semibold text-purple-700">Rs {{ number_format($creditProfit, 2) }}</p></div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="panel card-fade-in p-4">
                    <h3 class="font-semibold mb-3 flex items-center gap-2"><i class="bi bi-activity text-indigo-600"></i>Monthly Sales Trend</h3>
                    <canvas id="monthlySalesChart" height="120"></canvas>
                </div>
                <div class="panel card-fade-in p-4">
                    <h3 class="font-semibold mb-3 flex items-center gap-2"><i class="bi bi-pie-chart-fill text-indigo-600"></i>Cash vs Credit Sales</h3>
                    <canvas id="paymentSplitChart" height="120"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="panel card-fade-in p-4 lg:col-span-2">
                    <h3 class="font-semibold text-lg mb-3 flex items-center gap-2"><i class="bi bi-clock-history text-indigo-600"></i>Recent Sales</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Invoice</th>
                                    <th class="text-left py-2">Customer</th>
                                    <th class="text-left py-2">Type</th>
                                    <th class="text-left py-2">Total</th>
                                    <th class="text-left py-2">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentSales as $sale)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $sale->invoice_no }}</td>
                                        <td class="py-2">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                        <td class="py-2 capitalize">{{ $sale->payment_type }}</td>
                                        <td class="py-2">Rs {{ number_format($sale->total_amount, 2) }}</td>
                                        <td class="py-2">Rs {{ number_format($sale->due_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-3 text-gray-500">No sales yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="panel card-fade-in p-4">
                        <p class="text-sm text-gray-500 flex items-center gap-2"><i class="bi bi-hourglass-split text-red-500"></i>Total Pending Udhaar</p>
                        <p class="text-2xl font-bold text-red-600">Rs {{ number_format($totalPending, 2) }}</p>
                    </div>
                    <div class="panel card-fade-in p-4">
                        <p class="text-sm text-gray-500 flex items-center gap-2"><i class="bi bi-exclamation-triangle text-orange-500"></i>Low/Out Of Stock Items</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $lowStockProducts->count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Out of stock: {{ $outOfStockCount }}</p>
                    </div>
                    <div class="panel card-fade-in max-h-64 overflow-auto p-4">
                        <h4 class="font-semibold mb-2 flex items-center gap-2"><i class="bi bi-bell-fill text-orange-500"></i>Low Stock Alert</h4>
                        <ul class="text-sm space-y-1">
                            @forelse($lowStockProducts as $product)
                                <li>{{ $product->name }} ({{ $product->stock_quantity }} {{ $product->unit }})</li>
                            @empty
                                <li class="text-gray-500">No low stock alerts.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const monthlyLabels = @json($chartLabels);
        const monthlyTotals = @json($chartTotals);
        const paymentSplit = @json($paymentSplit);

        const monthlyCtx = document.getElementById('monthlySalesChart');
        if (monthlyCtx) {
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Sales Amount',
                        data: monthlyTotals,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.15)',
                        fill: true,
                        tension: 0.35
                    }]
                }
            });
        }

        const splitCtx = document.getElementById('paymentSplitChart');
        if (splitCtx) {
            new Chart(splitCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Cash', 'Credit'],
                    datasets: [{
                        data: paymentSplit,
                        backgroundColor: ['#059669', '#7c3aed']
                    }]
                }
            });
        }
    </script>
</x-app-layout>
