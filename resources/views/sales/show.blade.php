<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-file-earmark-text me-2"></i>Sale Details & Credit Recovery</h2></x-slot>
<div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
<p><strong>Invoice:</strong> {{ $sale->invoice_no }} | <strong>Date:</strong> {{ $sale->sale_date?->format('Y-m-d') }}</p>
<p><strong>Customer:</strong> {{ $sale->customer?->name ?? 'Walk-in' }} | <strong>Type:</strong> {{ strtoupper($sale->payment_type) }}</p>
<p><strong>Total:</strong> {{ number_format($sale->total_amount, 2) }} | <strong>Paid:</strong> {{ number_format($sale->paid_amount, 2) }} | <strong>Due:</strong> {{ number_format($sale->due_amount, 2) }}</p>
<p><a class="text-green-600 hover:text-green-700" href="{{ route('sales.invoice', $sale) }}"><i class="bi bi-file-earmark-pdf me-1"></i>Download Invoice PDF</a></p>
<table class="mt-4 min-w-full text-sm"><thead><tr class="border-b"><th class="p-2 text-left">Product</th><th class="p-2 text-left">Qty</th><th class="p-2 text-left">Unit Price</th><th class="p-2 text-left">Profit</th></tr></thead><tbody>@foreach($sale->items as $item)<tr class="border-b"><td class="p-2">{{ $item->product?->name }}</td><td class="p-2">{{ $item->quantity }}</td><td class="p-2">{{ number_format($item->unit_price,2) }}</td><td class="p-2">{{ number_format($item->profit_amount,2) }}</td></tr>@endforeach</tbody></table>
</div>
@if($sale->payment_type === 'credit')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
<h3 class="font-semibold mb-3"><i class="bi bi-wallet2 me-1"></i>Add Credit Payment (Partial / Full)</h3>
<form method="POST" action="{{ route('credit-payments.store') }}" class="grid md:grid-cols-4 gap-3">@csrf
<input type="hidden" name="sale_id" value="{{ $sale->id }}">
<div><label class="font-medium text-slate-700">Date</label><input type="date" name="payment_date" value="{{ now()->toDateString() }}" class="w-full"></div>
<div><label class="font-medium text-slate-700">Amount</label><input type="number" step="0.01" name="amount" class="w-full"></div>
<div class="md:col-span-2"><label class="font-medium text-slate-700">Note</label><input name="note" class="w-full"></div>
<div><button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg mt-6 shadow-sm"><i class="bi bi-plus-circle me-1"></i>Record Payment</button></div>
</form>
<h4 class="font-semibold mt-5">Payment History</h4>
<table class="mt-2 min-w-full text-sm"><thead><tr class="border-b"><th class="p-2 text-left">Date</th><th class="p-2 text-left">Amount</th><th class="p-2 text-left">By</th><th class="p-2 text-left">Note</th></tr></thead><tbody>@foreach($sale->creditPayments as $payment)<tr class="border-b"><td class="p-2">{{ $payment->payment_date?->format('Y-m-d') }}</td><td class="p-2">{{ number_format($payment->amount,2) }}</td><td class="p-2">{{ $payment->user?->name }}</td><td class="p-2">{{ $payment->note }}</td></tr>@endforeach</tbody></table>
</div>
@endif
</div>
</x-app-layout>
