<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Invoice</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; }
        .header { border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 14px; }
        .title { font-size: 20px; font-weight: 700; margin: 0; }
        .muted { color: #475569; font-size: 11px; }
        .meta p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f8fafc; font-weight: 700; color: #334155; }
        .right { text-align: right; }
        .totals { width: 45%; margin-left: auto; margin-top: 14px; }
        .totals td { border: 1px solid #e2e8f0; padding: 7px; }
        .totals .label { background: #f8fafc; font-weight: 700; }
        .totals .grand { font-weight: 700; background: #eef2ff; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Purchase Invoice</p>
        <p class="muted">Agri Inventory & Billing System</p>
    </div>
    <div class="meta">
        <p><strong>Invoice:</strong> {{ $purchase->invoice_no }}</p>
        <p><strong>Date:</strong> {{ $purchase->purchase_date?->format('Y-m-d') }}</p>
        <p><strong>Supplier:</strong> {{ $purchase->supplier?->name ?? 'N/A' }}</p>
        <p><strong>Created By:</strong> {{ $purchase->user?->name }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Cost</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
                <tr>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ number_format($item->unit_cost, 2) }}</td>
                    <td>{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="label grand">Total</td><td class="right grand">Rs {{ number_format($purchase->total_amount, 2) }}</td></tr>
    </table>
</body>
</html>
