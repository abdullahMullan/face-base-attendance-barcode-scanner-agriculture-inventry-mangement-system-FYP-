<x-app-layout>
	<x-slot name="header">
		<h2 class="title-gradient text-2xl font-semibold leading-tight"><i class="bi bi-bar-chart-line me-2"></i>Reports</h2>
	</x-slot>

	<div class="page-wrap space-y-6 py-8">
		<form method="GET" class="panel card-fade-in p-4 sm:p-5">
			<div class="flex flex-wrap items-end gap-3">
				<div>
					<label class="mb-1 block text-sm font-semibold text-slate-700">From</label>
					<input type="date" name="from" value="{{ $from?->format('Y-m-d') }}">
				</div>
				<div>
					<label class="mb-1 block text-sm font-semibold text-slate-700">To</label>
					<input type="date" name="to" value="{{ $to?->format('Y-m-d') }}">
				</div>
				<button class="btn-primary" type="submit"><i class="bi bi-funnel"></i>Filter</button>
				<a href="{{ route('reports.export.sales', ['from' => $from?->format('Y-m-d'), 'to' => $to?->format('Y-m-d')]) }}" class="btn-secondary"><i class="bi bi-filetype-csv"></i>Export Sales CSV</a>
				<a href="{{ route('reports.export.credit-customers') }}" class="btn-secondary"><i class="bi bi-file-earmark-text"></i>Credit Customers CSV</a>
				<a href="{{ route('reports.export.excel', ['from' => $from?->format('Y-m-d'), 'to' => $to?->format('Y-m-d')]) }}" class="btn-secondary"><i class="bi bi-file-earmark-excel"></i>Export Excel</a>
			</div>
		</form>

		<div class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-6">
			<div class="panel card-fade-in panel-hover p-4"><p class="text-xs text-slate-500">Total Sales</p><p class="text-lg font-bold text-slate-800">{{ number_format($summary['total_sales'],2) }}</p></div>
			<div class="panel card-fade-in panel-hover p-4"><p class="text-xs text-slate-500">Cash Sales</p><p class="text-lg font-bold text-slate-800">{{ number_format($summary['cash_sales'],2) }}</p></div>
			<div class="panel card-fade-in panel-hover p-4"><p class="text-xs text-slate-500">Credit Sales</p><p class="text-lg font-bold text-slate-800">{{ number_format($summary['credit_sales'],2) }}</p></div>
			<div class="panel card-fade-in panel-hover p-4"><p class="text-xs text-slate-500">Total Paid</p><p class="text-lg font-bold text-slate-800">{{ number_format($summary['total_paid'],2) }}</p></div>
			<div class="panel card-fade-in panel-hover p-4"><p class="text-xs text-slate-500">Pending</p><p class="text-lg font-bold text-red-600">{{ number_format($summary['total_pending'],2) }}</p></div>
			<div class="panel card-fade-in panel-hover p-4"><p class="text-xs text-slate-500">Credit Customers</p><p class="text-lg font-bold text-slate-800">{{ $summary['credit_customers'] }}</p></div>
		</div>

		<div class="panel card-fade-in p-4">
			<h3 class="section-title mb-3"><i class="bi bi-graph-up-arrow text-indigo-600"></i>Monthly Sales</h3>
			<div class="table-wrap border-0 shadow-none">
				<table class="min-w-full text-sm">
					<thead><tr class="border-b border-slate-200"><th class="p-2 text-left">Month</th><th class="p-2 text-left">Total</th></tr></thead>
					<tbody>@foreach($monthlySales as $row)<tr class="border-b border-slate-100"><td class="p-2">{{ $row->month }}</td><td class="p-2 font-semibold">{{ number_format($row->total,2) }}</td></tr>@endforeach</tbody>
				</table>
			</div>
		</div>

		<div class="panel p-4">
			<h3 class="section-title mb-3"><i class="bi bi-wallet2 text-amber-600"></i>Top Pending Credit Customers</h3>
			<div class="table-wrap border-0 shadow-none">
				<table class="min-w-full text-sm">
					<thead><tr class="border-b border-slate-200"><th class="p-2 text-left">Name</th><th class="p-2 text-left">Phone</th><th class="p-2 text-left">Pending</th></tr></thead>
					<tbody>@foreach($customerBalances as $customer)<tr class="border-b border-slate-100"><td class="p-2">{{ $customer->name }}</td><td class="p-2">{{ $customer->phone }}</td><td class="p-2 font-semibold text-red-600">{{ number_format($customer->due_total ?? 0,2) }}</td></tr>@endforeach</tbody>
				</table>
			</div>
		</div>

		<div class="panel p-4">
			<h3 class="section-title mb-3"><i class="bi bi-receipt text-indigo-600"></i>Sales in Date Range</h3>
			<div class="table-wrap border-0 shadow-none">
				<table class="min-w-full text-sm">
					<thead><tr class="border-b border-slate-200"><th class="p-2 text-left">Invoice</th><th class="p-2 text-left">Date</th><th class="p-2 text-left">Customer</th><th class="p-2 text-left">Type</th><th class="p-2 text-left">Total</th><th class="p-2 text-left">Due</th></tr></thead>
					<tbody>@foreach($sales as $sale)<tr class="border-b border-slate-100"><td class="p-2 font-medium">{{ $sale->invoice_no }}</td><td class="p-2">{{ $sale->sale_date?->format('Y-m-d') }}</td><td class="p-2">{{ $sale->customer?->name ?? 'Walk-in' }}</td><td class="p-2"><span class="badge-neutral">{{ strtoupper($sale->payment_type) }}</span></td><td class="p-2 font-semibold">{{ number_format($sale->total_amount,2) }}</td><td class="p-2 font-semibold {{ $sale->due_amount > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format($sale->due_amount,2) }}</td></tr>@endforeach</tbody>
				</table>
			</div>
			<div class="mt-3">{{ $sales->appends(request()->query())->links() }}</div>
		</div>

		<div class="panel p-4">
			<h3 class="section-title mb-3"><i class="bi bi-exclamation-triangle text-amber-600"></i>Low Stock Products</h3>
			<ul class="space-y-1 text-sm text-slate-700">
				@forelse($lowStockProducts as $product)
					<li>{{ $product->name }} - {{ $product->stock_quantity }} {{ $product->unit }}</li>
				@empty
					<li>No low stock products.</li>
				@endforelse
			</ul>
		</div>
	</div>
</x-app-layout>