<x-app-layout>
	<x-slot name="header">
		<h2 class="title-gradient text-2xl font-semibold leading-tight">
			<i class="bi bi-receipt-cutoff me-2"></i>Sales & Billing
		</h2>
	</x-slot>

	<div class="page-wrap py-8">
		<div class="panel card-fade-in mb-5 p-4 sm:p-5">
			<div class="flex flex-wrap gap-3">
				<a href="{{ route('sales.create') }}" class="btn-primary"><i class="bi bi-plus-circle"></i>New Sale</a>
				<a href="{{ route('customers.index') }}" class="btn-secondary"><i class="bi bi-people"></i>Manage Customers</a>
			</div>
			<form method="GET" action="{{ route('sales.index') }}" class="mt-4 flex flex-col gap-2 md:flex-row">
				<input type="text" name="q" value="{{ $search }}" placeholder="Search invoice, customer, phone, type" class="w-full md:max-w-md">
				<button class="btn-secondary" type="submit"><i class="bi bi-search"></i>Search</button>
				@if($search !== '')
					<a href="{{ route('sales.index') }}" class="btn-secondary">Reset</a>
				@endif
			</form>
		</div>

		<div class="table-wrap card-fade-in">
			<table class="min-w-full text-sm">
				<thead>
					<tr class="border-b border-slate-200">
						<th class="p-3 text-left">Invoice</th>
						<th class="p-3 text-left">Date</th>
						<th class="p-3 text-left">Customer</th>
						<th class="p-3 text-left">Type</th>
						<th class="p-3 text-left">Total</th>
						<th class="p-3 text-left">Due</th>
						<th class="p-3 text-left">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach($sales as $sale)
						<tr class="border-b border-slate-100">
							<td class="p-3 font-medium text-slate-800">{{ $sale->invoice_no }}</td>
							<td class="p-3">{{ $sale->sale_date?->format('Y-m-d') }}</td>
							<td class="p-3">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
							<td class="p-3">
								<span class="badge-neutral">{{ $sale->payment_type }}</span>
							</td>
							<td class="p-3 font-semibold text-slate-700">{{ number_format($sale->total_amount, 2) }}</td>
							<td class="p-3 font-semibold {{ $sale->due_amount > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format($sale->due_amount, 2) }}</td>
							<td class="p-3">
								<div class="flex flex-wrap items-center gap-3">
									<a href="{{ route('sales.show', $sale) }}" class="action-link-primary"><i class="bi bi-eye me-1"></i>View</a>
									<a href="{{ route('sales.invoice', $sale) }}" class="action-link-success"><i class="bi bi-file-earmark-pdf me-1"></i>Invoice PDF</a>
									<form method="POST" action="{{ route('sales.destroy', $sale) }}">
										@csrf
										@method('DELETE')
										<button class="action-link-danger"><i class="bi bi-trash me-1"></i>Delete</button>
									</form>
								</div>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		<div class="mt-4">{{ $sales->links() }}</div>
	</div>
</x-app-layout>