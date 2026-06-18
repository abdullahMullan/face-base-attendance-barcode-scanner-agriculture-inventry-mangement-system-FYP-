<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $purchases = Purchase::select('id', 'supplier_id', 'user_id', 'invoice_no', 'purchase_date', 'total_amount')
            ->with([
                'supplier:id,name,phone',
                'user:id,name',
                'items' => fn ($q) => $q->select('id', 'purchase_id', 'product_id', 'quantity', 'unit_cost', 'subtotal')->with('product:id,name')
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search): void {
                            $supplierQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('purchase_date')
            ->paginate(15)
            ->withQueryString();

        return view('purchases.index', compact('purchases', 'search'));
    }

    public function create(): View
    {
        $suppliers = Cache::remember('active_suppliers_list', now()->addHours(24), function () {
            return Supplier::select('id', 'name')->where('is_active', true)->orderBy('name')->get();
        });

        $categories = Cache::remember('purchases_categories_list', now()->addHours(24), function () {
            return Category::select('id', 'name')
                ->with(['products' => fn ($q) => $q->select('id', 'category_id', 'name', 'unit')->where('is_active', true)->orderBy('name')])
                ->orderBy('name')
                ->get();
        });

        $products = Cache::remember('purchases_products_list', now()->addHours(24), function () {
            return Product::select('id', 'category_id', 'name', 'brand', 'unit')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });

        return view('purchases.create', compact('suppliers', 'products', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $normalizedItems = [];
            $totalAmount = 0.0;

            foreach ($validated['items'] as $itemInput) {
                $product = Product::lockForUpdate()->findOrFail($itemInput['product_id']);
                $quantity = (float) $itemInput['quantity'];
                $unitCost = (float) $itemInput['unit_cost'];
                $lineSubtotal = round($quantity * $unitCost, 2);
                $totalAmount += $lineSubtotal;

                $normalizedItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $totalAmount = round($totalAmount, 2);

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'] ?? null,
                'user_id' => $request->user()->id,
                'invoice_no' => 'PUR-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($normalizedItems as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['product']->stock_quantity = (float) $item['product']->stock_quantity + $item['quantity'];
                $item['product']->purchase_price = $item['unit_cost'];
                $item['product']->save();
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded and stock updated.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        return redirect()->route('purchases.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        return redirect()->route('purchases.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase): void {
            $purchase->load('items.product');

            foreach ($purchase->items as $item) {
                $product = $item->product;
                $product->stock_quantity = max(0, (float) $product->stock_quantity - (float) $item->quantity);
                $product->save();
            }

            $purchase->delete();
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted and stock adjusted.');
    }

    public function invoice(Purchase $purchase): Response
    {
        $purchase->load(['supplier', 'user', 'items.product']);

        $pdf = Pdf::loadView('invoices.purchase', [
            'purchase' => $purchase,
        ])->setPaper('a4');

        return $pdf->download('purchase-invoice-'.$purchase->invoice_no.'.pdf');
    }
}
