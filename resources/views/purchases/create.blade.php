<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-bag-plus me-2"></i>New Purchase Entry</h2></x-slot>
<div class="page-wrap py-8">
<div class="mx-auto max-w-5xl">
@if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('purchases.store') }}" class="panel grid gap-4 p-6 md:grid-cols-2">
@csrf

<div>
    <label class="font-medium text-slate-700">Supplier (optional)</label>
    <select name="supplier_id" class="w-full">
        <option value="">--Select--</option>
        @foreach($suppliers as $supplier)
            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="font-medium text-slate-700">Purchase Date</label>
    <input type="date" name="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}" class="w-full">
</div>

<div class="md:col-span-2 border rounded-lg border-slate-200">
    <div class="flex items-center justify-between px-4 py-3 border-b bg-slate-50 rounded-t-lg">
        <h3 class="font-semibold text-slate-800">Purchase Items</h3>
        <button type="button" id="add_item_btn" class="btn-primary !px-3 !py-1.5 text-sm"><i class="bi bi-plus-circle me-1"></i>Add Item</button>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b bg-white">
                    <th class="p-3 text-left">Category</th>
                    <th class="p-3 text-left">Product</th>
                    <th class="p-3 text-left">Qty</th>
                    <th class="p-3 text-left">Unit Cost</th>
                    <th class="p-3 text-left">Subtotal</th>
                    <th class="p-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody id="purchase_items_tbody"></tbody>
        </table>
    </div>
</div>

<div class="md:col-span-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
    <p class="text-xs uppercase tracking-wide text-slate-500">Total Amount</p>
    <p id="summary_total" class="text-lg font-semibold text-slate-800">0.00</p>
</div>

<div class="md:col-span-2">
    <label class="font-medium text-slate-700">Notes</label>
    <textarea name="notes" rows="4" class="w-full">{{ old('notes') }}</textarea>
</div>
<div class="md:col-span-2 flex items-center gap-2">
    <button class="btn-primary"><i class="bi bi-check-circle me-1"></i>Save Purchase</button>
    <a href="{{ route('purchases.index') }}" class="btn-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
</form>

@php
    $categoriesForJs = $categories->map(fn ($category) => [
        'id' => $category->id,
        'name' => $category->name,
    ])->values();

    $productsForJs = $products->map(fn ($product) => [
        'id' => $product->id,
        'category_id' => $product->category_id,
        'name' => $product->name,
        'brand' => $product->brand,
        'unit' => $product->unit,
    ])->values();
@endphp

<script>
    const categories = @json($categoriesForJs);
    const products = @json($productsForJs);

    const tbody = document.getElementById('purchase_items_tbody');
    const addItemBtn = document.getElementById('add_item_btn');
    const oldItems = @json(old('items', []));

    function formatMoney(value) {
        return Number(value || 0).toFixed(2);
    }

    function getProductsByCategory(categoryId) {
        return products.filter((product) => String(product.category_id) === String(categoryId));
    }

    function updateRowProductOptions(row, selectedProductId = '') {
        const categorySelect = row.querySelector('.js-category');
        const productSelect = row.querySelector('.js-product');
        const categoryProducts = getProductsByCategory(categorySelect.value);

        productSelect.innerHTML = '<option value="">Select product</option>';
        categoryProducts.forEach((product) => {
            const option = document.createElement('option');
            option.value = product.id;
            const suffix = product.brand ? ` - ${product.brand}` : '';
            option.textContent = `${product.name}${suffix}`;
            productSelect.appendChild(option);
        });

        if (selectedProductId && categoryProducts.some((product) => String(product.id) === String(selectedProductId))) {
            productSelect.value = selectedProductId;
        }
    }

    function syncRowIndexes() {
        Array.from(tbody.querySelectorAll('tr')).forEach((row, index) => {
            row.querySelector('.js-product').name = `items[${index}][product_id]`;
            row.querySelector('.js-qty').name = `items[${index}][quantity]`;
            row.querySelector('.js-unit-cost').name = `items[${index}][unit_cost]`;
        });
    }

    function updateRowDisplay(row) {
        const qtyInput = row.querySelector('.js-qty');
        const unitCostInput = row.querySelector('.js-unit-cost');
        const subtotalCell = row.querySelector('.js-subtotal');

        const quantity = Number(qtyInput.value || 0);
        const unitCost = Number(unitCostInput.value || 0);
        subtotalCell.textContent = formatMoney(quantity * unitCost);
    }

    function recalcSummary() {
        let total = 0;
        Array.from(tbody.querySelectorAll('tr')).forEach((row) => {
            total += Number(row.querySelector('.js-subtotal').textContent || 0);
        });

        document.getElementById('summary_total').textContent = formatMoney(total);
    }

    function bindRowEvents(row) {
        const categorySelect = row.querySelector('.js-category');
        const productSelect = row.querySelector('.js-product');
        const qtyInput = row.querySelector('.js-qty');
        const unitCostInput = row.querySelector('.js-unit-cost');
        const removeBtn = row.querySelector('.js-remove-item');

        categorySelect.addEventListener('change', () => {
            updateRowProductOptions(row);
        });

        productSelect.addEventListener('change', () => {
            recalcSummary();
        });

        qtyInput.addEventListener('input', () => {
            updateRowDisplay(row);
            recalcSummary();
        });

        unitCostInput.addEventListener('input', () => {
            updateRowDisplay(row);
            recalcSummary();
        });

        removeBtn.addEventListener('click', () => {
            if (tbody.querySelectorAll('tr').length === 1) {
                return;
            }

            row.remove();
            syncRowIndexes();
            recalcSummary();
        });
    }

    function addRow(item = {}) {
        const row = document.createElement('tr');
        row.className = 'border-b';

        const defaultCategory = item.category_id || categories[0]?.id || '';
        row.innerHTML = `
            <td class="p-3">
                <select class="w-full js-category" required>
                    ${categories.map((category) => `<option value="${category.id}">${category.name}</option>`).join('')}
                </select>
            </td>
            <td class="p-3"><select class="w-full js-product" required></select></td>
            <td class="p-3"><input type="number" step="0.01" min="0.01" class="w-28 js-qty" value="${item.quantity || 1}" required></td>
            <td class="p-3"><input type="number" step="0.01" min="0" class="w-32 js-unit-cost" value="${item.unit_cost || 0}" required></td>
            <td class="p-3 js-subtotal">0.00</td>
            <td class="p-3"><button type="button" class="text-red-600 js-remove-item"><i class="bi bi-trash me-1"></i>Remove</button></td>
        `;

        tbody.appendChild(row);

        const categorySelect = row.querySelector('.js-category');
        if (defaultCategory) {
            categorySelect.value = String(defaultCategory);
        }

        const selectedProductId = item.product_id || '';
        updateRowProductOptions(row, selectedProductId);

        // Only set product if explicitly provided (editing/old items)
        if (selectedProductId) {
            row.querySelector('.js-product').value = String(selectedProductId);
        }

        bindRowEvents(row);
        updateRowDisplay(row);
        syncRowIndexes();
        recalcSummary();
    }

    addItemBtn.addEventListener('click', () => addRow());

    if (oldItems.length > 0) {
        oldItems.forEach((item) => {
            const selectedProduct = products.find((product) => String(product.id) === String(item.product_id));
            addRow({
                category_id: selectedProduct?.category_id,
                product_id: item.product_id,
                quantity: item.quantity,
                unit_cost: item.unit_cost,
            });
        });
    } else {
        addRow();
    }
</script>
</div>
</div>
</x-app-layout>
