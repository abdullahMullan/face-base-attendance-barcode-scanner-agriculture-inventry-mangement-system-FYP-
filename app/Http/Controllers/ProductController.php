<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index()
    {
        $search = trim((string) request()->query('q', ''));

        $products = Product::select('id', 'category_id', 'name', 'type', 'brand', 'unit', 'stock_quantity', 'low_stock_threshold', 'purchase_price', 'selling_price', 'is_active', 'image_path')
            ->with('category:id,name')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($search): void {
                            $categoryQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
        $lowStockProducts = Cache::remember('low_stock_count', now()->addMinutes(5), function () {
            return Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        });

        return view('products.index', compact('products', 'lowStockProducts', 'search'));
    }

    public function create()
    {
        $categories = Cache::remember('product_categories_list', now()->addHours(24), function () {
            return Category::select('id', 'name')->orderBy('name')->get();
        });

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'gte:purchase_price'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];

        // Only validate barcode uniqueness if the DB column exists (migration run)
        if (Schema::hasColumn('products', 'barcode')) {
            $rules['barcode'] = ['nullable', 'string', 'max:100', 'unique:products,barcode'];
        } else {
            // accept barcode as input but do not validate uniqueness yet
            $rules['barcode'] = ['nullable', 'string', 'max:100'];
        }

        $validated = $request->validate($rules);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image_path'] = $imagePath;
        }

        Product::create($validated);
        Cache::forget('product_categories_list');
        Cache::forget('sales_products_list');
        Cache::forget('purchases_products_list');

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return redirect()->route('products.edit', $product);
    }

    public function edit(Product $product)
    {
        $categories = Cache::remember('product_categories_list', now()->addHours(24), function () {
            return Category::select('id', 'name')->orderBy('name')->get();
        });

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'gte:purchase_price'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];

        if (Schema::hasColumn('products', 'barcode')) {
            $rules['barcode'] = ['nullable', 'string', 'max:100', 'unique:products,barcode,'. $product->id];
        } else {
            $rules['barcode'] = ['nullable', 'string', 'max:100'];
        }

        $validated = $request->validate($rules);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image_path'] = $imagePath;
        }

        $product->update($validated);
        Cache::forget('product_categories_list');
        Cache::forget('sales_products_list');
        Cache::forget('purchases_products_list');

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
            return back()->withErrors('Cannot delete product with transaction history.');
        }

        // Delete image if it exists
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();
        Cache::forget('product_categories_list');
        Cache::forget('sales_products_list');
        Cache::forget('purchases_products_list');

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}

