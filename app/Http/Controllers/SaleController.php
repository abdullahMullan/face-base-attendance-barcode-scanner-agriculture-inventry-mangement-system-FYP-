<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $sales = Sale::select('id', 'customer_id', 'user_id', 'invoice_no', 'sale_date', 'payment_type', 'total_amount', 'paid_amount', 'due_amount')
            ->with([
                'customer:id,name,phone',
                'user:id,name',
                'items' => fn ($q) => $q->select('id', 'sale_id', 'product_id', 'quantity', 'unit_price', 'subtotal')->with('product:id,name')
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('invoice_no', 'like', "%{$search}%")
                        ->orWhere('payment_type', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search): void {
                            $customerQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('sale_date')
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', compact('sales', 'search'));
    }

    public function create(): View
    {
        $categories = Cache::remember('sales_categories_list', now()->addHours(24), function () {
            return Category::select('id', 'name')
                ->with(['products' => fn ($q) => $q->select('id', 'category_id', 'name', 'unit')->where('is_active', true)->orderBy('name')])
                ->orderBy('name')
                ->get();
        });

        // Cache products list separately for quick access
        $products = Cache::remember('sales_products_list', now()->addHours(24), function () {
            return Product::select('id', 'category_id', 'name', 'stock_quantity', 'selling_price', 'purchase_price', 'unit')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });

        $customers = Cache::remember('active_customers_list', now()->addHours(24), function () {
            return Customer::select('id', 'name', 'phone')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });

        return view('sales.create', compact('products', 'customers', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'sale_date' => ['required', 'date'],
            'payment_type' => ['required', 'in:cash,partial,credit'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'new_customer_name' => ['nullable', 'string', 'max:255'],
            'new_customer_phone' => ['nullable', 'string', 'max:30'],
            'new_customer_address' => ['nullable', 'string', 'max:1000'],
        ], [
            'new_customer_name.required' => 'Credit sale ke liye customer name zaroori hai.',
        ]);

        if (
            ($validated['payment_type'] === 'credit' || $validated['payment_type'] === 'partial')
            && empty($validated['customer_id'])
            && empty($validated['new_customer_name'])
        ) {
            throw ValidationException::withMessages([
                'new_customer_name' => 'Credit/Partial sale ke liye existing customer select karein ya new customer name likhein.',
            ]);
        }

        DB::transaction(function () use ($validated, $request): void {
            $resolvedCustomerId = $validated['customer_id'] ?? null;

            $normalizedItems = [];
            $subtotal = 0.0;

            foreach ($validated['items'] as $index => $itemInput) {
                $product = Product::lockForUpdate()->findOrFail($itemInput['product_id']);
                $quantity = (float) $itemInput['quantity'];

                if ((float) $product->stock_quantity < $quantity) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => 'Insufficient stock for '.$product->name.'. Available: '.$product->stock_quantity,
                    ]);
                }

                $lineSubtotal = round($quantity * (float) $product->selling_price, 2);
                $lineProfit = round(((float) $product->selling_price - (float) $product->purchase_price) * $quantity, 2);
                $subtotal += $lineSubtotal;

                $normalizedItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_cost' => (float) $product->purchase_price,
                    'unit_price' => (float) $product->selling_price,
                    'subtotal' => $lineSubtotal,
                    'profit_amount' => $lineProfit,
                ];
            }

            if ($validated['payment_type'] === 'credit' && empty($resolvedCustomerId)) {
                $customer = Customer::firstOrCreate(
                    [
                        'name' => trim((string) $validated['new_customer_name']),
                        'phone' => $validated['new_customer_phone'] ?: null,
                    ],
                    [
                        'address' => $validated['new_customer_address'] ?? null,
                        'is_active' => true,
                    ]
                );

                $resolvedCustomerId = $customer->id;
            }

            $discount = (float) ($validated['discount'] ?? 0);
            $subtotal = round($subtotal, 2);
            $total = max(0, $subtotal - $discount);

            $paidAmount = $validated['payment_type'] === 'cash'
                ? $total
                : min((float) ($validated['paid_amount'] ?? 0), $total);

            $dueAmount = round($total - $paidAmount, 2);

            $sale = Sale::create([
                'customer_id' => $resolvedCustomerId,
                'user_id' => $request->user()->id,
                'invoice_no' => 'SAL-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'sale_date' => $validated['sale_date'],
                'payment_type' => $validated['payment_type'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $total,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            $sale->updatePaymentStatus();
            $sale->save();

            foreach ($normalizedItems as $item) {
                $sale->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'profit_amount' => $item['profit_amount'],
                ]);

                $item['product']->stock_quantity = (float) $item['product']->stock_quantity - $item['quantity'];
                $item['product']->save();
            }

            if (($validated['payment_type'] === 'credit' || $validated['payment_type'] === 'partial') && $paidAmount > 0) {
                $sale->creditPayments()->create([
                    'customer_id' => $sale->customer_id,
                    'user_id' => $request->user()->id,
                    'payment_date' => $validated['sale_date'],
                    'amount' => $paidAmount,
                    'note' => 'Initial partial payment at sale time',
                ]);
            }

        });

        return redirect()->route('sales.index')->with('success', 'Sale recorded and inventory updated.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product', 'creditPayments.user']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        return redirect()->route('sales.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        return redirect()->route('sales.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale): void {
            $sale->load('items.product');

            foreach ($sale->items as $item) {
                $product = $item->product;
                $product->stock_quantity = (float) $product->stock_quantity + (float) $item->quantity;
                $product->save();
            }

            $sale->delete();
        });

        return redirect()->route('sales.index')->with('success', 'Sale deleted and inventory restored.');
    }

    public function invoice(Sale $sale): Response
    {
        $sale->load(['customer', 'user', 'items.product', 'creditPayments.user']);

        $pdf = Pdf::loadView('invoices.sale', [
            'sale' => $sale,
        ])->setPaper('a4');

        return $pdf->download('sale-invoice-'.$sale->invoice_no.'.pdf');
    }
}
