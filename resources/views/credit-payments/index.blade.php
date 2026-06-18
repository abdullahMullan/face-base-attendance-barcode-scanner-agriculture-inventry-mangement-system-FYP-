<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Credit Payments</h2></x-slot>
<div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
<form method="GET" action="{{ route('credit-payments.index') }}" class="mb-4 flex gap-2">
	<input type="text" name="q" value="{{ $search }}" placeholder="Search invoice, customer, user" class="w-full md:w-96 rounded border-slate-300">
	<button class="px-4 py-2 bg-slate-700 text-white rounded">Search</button>
	@if($search !== '')<a href="{{ route('credit-payments.index') }}" class="px-4 py-2 bg-slate-100 rounded">Reset</a>@endif
</form>
<div class="bg-white rounded shadow overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="border-b"><th class="p-3 text-left">Date</th><th class="p-3 text-left">Invoice</th><th class="p-3 text-left">Customer</th><th class="p-3 text-left">Amount</th><th class="p-3 text-left">Received By</th></tr></thead><tbody>@foreach($payments as $payment)<tr class="border-b"><td class="p-3">{{ $payment->payment_date?->format('Y-m-d') }}</td><td class="p-3">{{ $payment->sale?->invoice_no }}</td><td class="p-3">{{ $payment->customer?->name ?? '-' }}</td><td class="p-3">{{ number_format($payment->amount,2) }}</td><td class="p-3">{{ $payment->user?->name }}</td></tr>@endforeach</tbody></table></div>
<div class="mt-4">{{ $payments->links() }}</div>
</div>
</x-app-layout>