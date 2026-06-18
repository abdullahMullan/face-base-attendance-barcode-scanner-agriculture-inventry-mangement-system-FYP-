<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-file-earmark-medical me-2"></i>Purchase Details</h2></x-slot>
<div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
<p><strong>Invoice:</strong> {{ $purchase->invoice_no }}</p>
<p><strong>Date:</strong> {{ $purchase->purchase_date?->format('Y-m-d') }}</p>
<p><strong>Supplier:</strong> {{ $purchase->supplier?->name ?? '-' }}</p>
<p><strong>Total:</strong> {{ number_format($purchase->total_amount,2) }}</p>
<p><a class="text-green-600 hover:text-green-700" href="{{ route('purchases.invoice', $purchase) }}"><i class="bi bi-file-earmark-pdf me-1"></i>Download Invoice PDF</a></p>
<table class="mt-4 min-w-full text-sm"><thead><tr class="border-b"><th class="p-2 text-left">Product</th><th class="p-2 text-left">Qty</th><th class="p-2 text-left">Unit Cost</th><th class="p-2 text-left">Subtotal</th></tr></thead><tbody>@foreach($purchase->items as $item)<tr class="border-b"><td class="p-2">{{ $item->product?->name }}</td><td class="p-2">{{ $item->quantity }}</td><td class="p-2">{{ number_format($item->unit_cost,2) }}</td><td class="p-2">{{ number_format($item->subtotal,2) }}</td></tr>@endforeach</tbody></table>
<div class="mt-4"><a href="{{ route('purchases.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-700"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
</div>
</div>
</x-app-layout>
