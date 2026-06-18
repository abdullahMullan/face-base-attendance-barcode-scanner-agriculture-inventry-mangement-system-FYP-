<x-app-layout>
	<x-slot name="header">
		<h2 class="title-gradient text-2xl font-semibold leading-tight">
			<i class="bi bi-bag-check me-2"></i>Purchases
		</h2>
	</x-slot>

	<div class="page-wrap py-8">
		<div class="panel card-fade-in mb-5 p-4 sm:p-5">
			<div class="flex flex-wrap gap-3">
				<a href="{{ route('purchases.create') }}" class="btn-primary"><i class="bi bi-plus-circle"></i>New Purchase</a>
				<a href="{{ route('suppliers.index') }}" class="btn-secondary"><i class="bi bi-truck"></i>Manage Suppliers</a>
			</div>
			<form method="GET" action="{{ route('purchases.index') }}" class="mt-4 flex flex-col gap-2 md:flex-row">
				<input type="text" name="q" value="{{ $search }}" placeholder="Search invoice, supplier, phone" class="w-full md:max-w-md">
				<button class="btn-secondary" type="submit"><i class="bi bi-search"></i>Search</button>
				@if($search !== '')
					<a href="{{ route('purchases.index') }}" class="btn-secondary">Reset</a>
				@endif
			</form>
		</div>

		@php
			$pageInvoices = $purchases->count();
			$pageItemLines = $purchases->sum(fn ($purchase) => $purchase->items->count());
			$pageTotalQty = $purchases->sum(fn ($purchase) => $purchase->items->sum('quantity'));
		@endphp

		<div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
			<div class="panel card-fade-in p-4 panel-hover"><p class="text-xs uppercase tracking-wide text-slate-500">Invoices (page)</p><p class="text-xl font-bold text-slate-800">{{ $pageInvoices }}</p></div>
			<div class="panel card-fade-in p-4 panel-hover"><p class="text-xs uppercase tracking-wide text-slate-500">Item Lines</p><p class="text-xl font-bold text-slate-800">{{ $pageItemLines }}</p></div>
			<div class="panel card-fade-in p-4 panel-hover"><p class="text-xs uppercase tracking-wide text-slate-500">Total Qty</p><p class="text-xl font-bold text-slate-800">{{ number_format($pageTotalQty, 2) }}</p></div>
		</div>

		<div class="table-wrap card-fade-in">
			<table class="min-w-full text-sm">
				<thead>
					<tr class="border-b border-slate-200">
						<th class="p-3 text-left">Invoice</th>
						<th class="p-3 text-left">Date</th>
						<th class="p-3 text-left">Supplier</th>
						<th class="p-3 text-left">Items</th>
						<th class="p-3 text-left">Qty</th>
						<th class="p-3 text-left">Total</th>
						<th class="p-3 text-left">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach($purchases as $purchase)
						<tr class="border-b border-slate-100">
							<td class="p-3 font-medium text-slate-800">{{ $purchase->invoice_no }}</td>
							<td class="p-3">{{ $purchase->purchase_date?->format('Y-m-d') }}</td>
							<td class="p-3">{{ $purchase->supplier?->name ?? '-' }}</td>
							<td class="p-3">{{ $purchase->items->count() }}</td>
							<td class="p-3">{{ number_format($purchase->items->sum('quantity'), 2) }}</td>
							<td class="p-3 font-semibold text-slate-700">{{ number_format($purchase->total_amount, 2) }}</td>
							<td class="p-3">
								<div class="flex flex-wrap items-center gap-3">
									<a class="action-link-primary" href="{{ route('purchases.show', $purchase) }}"><i class="bi bi-eye me-1"></i>View</a>
									<a class="action-link-success" href="{{ route('purchases.invoice', $purchase) }}"><i class="bi bi-file-earmark-pdf me-1"></i>Invoice PDF</a>
									<form method="POST" action="{{ route('purchases.destroy', $purchase) }}">
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
		<div class="mt-4">{{ $purchases->links() }}</div>
	</div>
</x-app-layout>