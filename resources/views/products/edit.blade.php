<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-pencil-square me-2"></i>Edit Product</h2></x-slot>
    <div class="page-wrap py-8">
        <div class="mx-auto max-w-4xl">
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="panel grid gap-4 p-6 md:grid-cols-2">
            @csrf @method('PUT')
            <div><label class="font-medium text-slate-700">Category</label><select name="category_id" class="w-full">@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div><label class="font-medium text-slate-700">Name</label><input name="name" class="w-full" value="{{ old('name', $product->name) }}"></div>
            <div><label class="font-medium text-slate-700">Type</label><input name="type" class="w-full" value="{{ old('type', $product->type) }}"></div>
            <div><label class="font-medium text-slate-700">Brand</label><input name="brand" class="w-full" value="{{ old('brand', $product->brand) }}"></div>
            <div>
                <label class="font-medium text-slate-700">Barcode <span class="text-gray-500 text-sm">(optional)</span></label>
                <input name="barcode" class="w-full" value="{{ old('barcode', $product->barcode) }}">
                @if($product->barcode)
                    <div class="mt-2 flex items-center gap-2">
                        <img src="{{ route('products.barcode', $product) }}" alt="Barcode" class="h-16">
                        <a href="{{ route('products.barcode', $product) }}" class="btn-secondary" target="_blank">Open</a>
                    </div>
                @endif
            </div>
            <div><label class="font-medium text-slate-700">Unit</label><input name="unit" class="w-full" value="{{ old('unit', $product->unit) }}"></div>
            <div><label class="font-medium text-slate-700">Stock Qty</label><input type="number" step="0.01" name="stock_quantity" class="w-full" value="{{ old('stock_quantity', $product->stock_quantity) }}"></div>
            <div><label class="font-medium text-slate-700">Low Stock Threshold</label><input type="number" step="0.01" name="low_stock_threshold" class="w-full" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}"></div>
            <div><label class="font-medium text-slate-700">Purchase Price</label><input type="number" step="0.01" name="purchase_price" class="w-full" value="{{ old('purchase_price', $product->purchase_price) }}"></div>
            <div><label class="font-medium text-slate-700">Selling Price</label><input type="number" step="0.01" name="selling_price" class="w-full" value="{{ old('selling_price', $product->selling_price) }}"></div>
            <div><label class="font-medium text-slate-700">Status</label><select name="is_active" class="w-full"><option value="1" @selected(old('is_active', $product->is_active)==1)>Active</option><option value="0" @selected(old('is_active', $product->is_active)==0)>Inactive</option></select></div>
            <div class="md:col-span-2">
                <label class="font-medium text-slate-700">Product Image <span class="text-gray-500 text-sm">(Optional)</span></label>
                @if($product->image_path)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="Product image" class="h-40 w-40 rounded object-cover">
                        <p class="text-sm text-gray-600 mt-2">Current image - Upload a new image to replace</p>
                    </div>
                @endif
                <input type="file" name="image" accept="image/*" class="w-full">
            </div>
            <div class="md:col-span-2 flex items-center gap-2"><button class="btn-primary"><i class="bi bi-save me-1"></i>Update</button><a href="{{ route('products.index') }}" class="btn-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
        </form>
        </div>
    </div>
</x-app-layout>
