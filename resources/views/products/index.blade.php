<x-app-layout>
    <x-slot name="header">
        <h2 class="title-gradient text-2xl font-semibold leading-tight">
            <i class="bi bi-box-seam me-2"></i>Products
        </h2>
    </x-slot>

    <div class="page-wrap py-8">
        <div class="panel card-fade-in mb-5 p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('products.create') }}" class="btn-primary">
                    <i class="bi bi-plus-circle"></i>Add Product
                </a>
                <span class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-700">
                    <i class="bi bi-exclamation-triangle"></i>Low Stock Alerts: {{ $lowStockProducts }}
                </span>
            </div>
            <form method="GET" action="{{ route('products.index') }}" class="mt-4 flex flex-col gap-2 md:flex-row">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search product, type, brand, category" class="w-full md:max-w-md">
                <button class="btn-secondary" type="submit"><i class="bi bi-search"></i>Search</button>
                @if($search !== '')
                    <a href="{{ route('products.index') }}" class="btn-secondary">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-wrap card-fade-in">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="p-3 text-left">Image</th>
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Category</th>
                        <th class="p-3 text-left">Stock</th>
                        <th class="p-3 text-left">Sell Price</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr class="border-b border-slate-100">
                            <td class="p-3">
                                @if($product->image_path)
                                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="h-10 w-10 rounded object-cover">
                                @else
                                    <div class="h-10 w-10 flex items-center justify-center bg-gray-200 rounded text-gray-600">
                                        <i class="bi bi-image text-sm"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="p-3 font-medium text-slate-800">{{ $product->name }}</td>
                            <td class="p-3">{{ $product->category?->name }}</td>
                            <td class="p-3">{{ $product->stock_quantity }} {{ $product->unit }}</td>
                            <td class="p-3 font-semibold text-slate-700">{{ number_format($product->selling_price, 2) }}</td>
                            <td class="p-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="{{ route('products.edit', $product) }}" class="action-link-primary">Edit</a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="action-link-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    </div>
</x-app-layout>
