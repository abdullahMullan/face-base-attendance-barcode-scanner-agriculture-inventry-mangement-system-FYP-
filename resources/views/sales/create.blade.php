<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-receipt me-2"></i>New Sale</h2></x-slot>
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

<form method="POST" action="{{ route('sales.store') }}" class="panel grid gap-4 p-6 md:grid-cols-2">
@csrf

<div>
    <label class="font-medium text-slate-700">Sale Date</label>
    <input type="date" name="sale_date" value="{{ old('sale_date', now()->toDateString()) }}" class="w-full">
</div>
<div>
    <label class="font-medium text-slate-700">Payment Type</label>
    <select id="payment_type" name="payment_type" class="w-full">
        <option value="cash" @selected(old('payment_type', 'cash') === 'cash')>Cash Payment (Full)</option>
        <option value="partial" @selected(old('payment_type') === 'partial')>Partial Payment</option>
        <option value="credit" @selected(old('payment_type') === 'credit')>Credit (Due)</option>
    </select>
</div>

<div class="md:col-span-2 border rounded-lg border-slate-200">
    <div class="flex items-center justify-between px-4 py-3 border-b bg-slate-50 rounded-t-lg">
        <h3 class="font-semibold text-slate-800">Sale Items</h3>
        <div class="flex items-center gap-2">
            <button type="button" id="open_scanner_btn" onclick="window.startSaleScanner && window.startSaleScanner(); return false;" class="btn-secondary !px-3 !py-1.5 text-sm"><i class="bi bi-upc-scan me-1"></i>Scan Barcode</button>
            <button type="button" id="add_item_btn" class="btn-primary !px-3 !py-1.5 text-sm"><i class="bi bi-plus-circle me-1"></i>Add Item</button>
        </div>
    </div>
    <div id="scanner_panel" class="hidden border-b bg-slate-50 px-4 py-4">
        <div class="flex items-center justify-between gap-3 mb-3">
            <div>
                <h4 class="font-semibold text-slate-800">Camera Scanner</h4>
                <p class="text-sm text-slate-600">Laptop webcam ya mobile camera dono se barcode scan ho sakta hai.</p>
            </div>
            <button type="button" id="close_scanner_btn" onclick="window.stopSaleScanner && window.stopSaleScanner(); return false;" class="btn-secondary !px-3 !py-1.5 text-sm"><i class="bi bi-x-lg me-1"></i>Close</button>
        </div>
        <div id="scanner_status" class="mb-3 text-sm text-slate-600">Camera ready.</div>
        <div id="scanner_reader" class="mx-auto max-w-md rounded-xl overflow-hidden border border-slate-200 bg-white"></div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b bg-white">
                    <th class="p-3 text-left">Category</th>
                    <th class="p-3 text-left">Product</th>
                    <th class="p-3 text-left">Stock</th>
                    <th class="p-3 text-left">Price</th>
                    <th class="p-3 text-left">Qty</th>
                    <th class="p-3 text-left">Subtotal</th>
                    <th class="p-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody id="sale_items_tbody"></tbody>
        </table>
    </div>
</div>

<div>
    <label class="font-medium text-slate-700">Discount</label>
    <input type="number" step="0.01" min="0" id="discount" name="discount" value="{{ old('discount', 0) }}" class="w-full">
</div>
<div>
    <label class="font-medium text-slate-700">Paid Amount (remaining becomes credit/due)</label>
    <input type="number" step="0.01" min="0" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', 0) }}" placeholder="Enter paid amount" class="w-full">
</div>

<div class="md:col-span-2 grid md:grid-cols-3 gap-3">
    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
        <p class="text-xs uppercase tracking-wide text-slate-500">Subtotal</p>
        <p id="summary_subtotal" class="text-lg font-semibold text-slate-800">0.00</p>
    </div>
    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
        <p class="text-xs uppercase tracking-wide text-slate-500">Total</p>
        <p id="summary_total" class="text-lg font-semibold text-slate-800">0.00</p>
    </div>
    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
        <p class="text-xs uppercase tracking-wide text-slate-500">Due</p>
        <p id="summary_due" class="text-lg font-semibold text-slate-800">0.00</p>
    </div>
</div>

<div>
    <label class="font-medium text-slate-700">Existing Customer (optional)</label>
    <select id="customer_id" name="customer_id" class="w-full">
        <option value="">On-spot new customer</option>
        @foreach($customers as $customer)
            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }} - {{ $customer->phone }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="font-medium text-slate-700">New Customer Name (on-spot)</label>
    <input id="new_customer_name" name="new_customer_name" value="{{ old('new_customer_name') }}" class="w-full" placeholder="e.g. Ahmad Ali">
</div>
<div>
    <label class="font-medium text-slate-700">New Customer Phone</label>
    <input id="new_customer_phone" name="new_customer_phone" value="{{ old('new_customer_phone') }}" class="w-full" placeholder="03xxxxxxxxx">
</div>
<div>
    <label class="font-medium text-slate-700">New Customer Address</label>
    <input id="new_customer_address" name="new_customer_address" value="{{ old('new_customer_address') }}" class="w-full" placeholder="Village / City">
</div>

<div class="md:col-span-2">
    <label class="font-medium text-slate-700">Notes</label>
    <textarea name="notes" rows="4" class="w-full">{{ old('notes') }}</textarea>
</div>
<div id="credit_notice" class="md:col-span-2 text-sm text-amber-700 hidden"><i class="bi bi-info-circle me-1"></i>Credit sale ke liye existing customer select karein ya new customer name likhein.</div>
<div class="md:col-span-2 flex items-center gap-2">
    <button class="btn-primary"><i class="bi bi-check-circle me-1"></i>Save Sale</button>
    <a href="{{ route('sales.index') }}" class="btn-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
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
        'stock_quantity' => (float) $product->stock_quantity,
        'selling_price' => (float) $product->selling_price,
        'unit' => $product->unit,
    ])->values();
@endphp

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const categories = @json($categoriesForJs);
        const products = @json($productsForJs);
        const barcodeLookupEndpoint = @json(url('/product-by-barcode'));

        const tbody = document.getElementById('sale_items_tbody');
        const addItemBtn = document.getElementById('add_item_btn');
        const openScannerBtn = document.getElementById('open_scanner_btn');
        const closeScannerBtn = document.getElementById('close_scanner_btn');
        const scannerPanel = document.getElementById('scanner_panel');
        const scannerStatus = document.getElementById('scanner_status');
        const discountInput = document.getElementById('discount');
        const paidInput = document.getElementById('paid_amount');

        const paymentType = document.getElementById('payment_type');
        const customerSelect = document.getElementById('customer_id');
        const newCustomerName = document.getElementById('new_customer_name');
        const creditNotice = document.getElementById('credit_notice');

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
            option.textContent = `${product.name} (Stock: ${product.stock_quantity})`;
            productSelect.appendChild(option);
        });

        if (selectedProductId && categoryProducts.some((product) => String(product.id) === String(selectedProductId))) {
            productSelect.value = selectedProductId;
        }
    }

    function updateRowDisplay(row) {
        const productSelect = row.querySelector('.js-product');
        const qtyInput = row.querySelector('.js-qty');
        const stockCell = row.querySelector('.js-stock');
        const priceCell = row.querySelector('.js-price');
        const subtotalCell = row.querySelector('.js-subtotal');

        const product = products.find((entry) => String(entry.id) === productSelect.value);
        
        if (!product) {
            // No product selected - show empty/dash
            stockCell.textContent = '-';
            priceCell.textContent = '0.00';
            subtotalCell.textContent = '0.00';
            return;
        }
        
        const quantity = Number(qtyInput.value || 0);
        const unitPrice = product ? Number(product.selling_price) : 0;
        const subtotal = unitPrice * quantity;

        stockCell.textContent = `${product.stock_quantity} ${product.unit}`;
        priceCell.textContent = formatMoney(unitPrice);
        subtotalCell.textContent = formatMoney(subtotal);
    }

    function findRowByProductId(productId) {
        return Array.from(tbody.querySelectorAll('tr')).find((row) => row.querySelector('.js-product').value === String(productId));
    }

    function addOrIncrementProduct(product) {
        const existingRow = findRowByProductId(product.id);

        if (existingRow) {
            const qtyInput = existingRow.querySelector('.js-qty');
            qtyInput.value = Number(qtyInput.value || 0) + 1;
            updateRowDisplay(existingRow);
            recalcSummary();
            return;
        }

        addRow({
            category_id: product.category_id,
            product_id: product.id,
            quantity: 1,
        });
    }

    function syncRowIndexes() {
        Array.from(tbody.querySelectorAll('tr')).forEach((row, index) => {
            row.querySelector('.js-product').name = `items[${index}][product_id]`;
            row.querySelector('.js-qty').name = `items[${index}][quantity]`;
        });
    }

    function recalcSummary() {
        let subtotal = 0;
        Array.from(tbody.querySelectorAll('tr')).forEach((row) => {
            const lineSubtotal = Number(row.querySelector('.js-subtotal').textContent || 0);
            subtotal += lineSubtotal;
        });

        const discount = Math.max(0, Number(discountInput.value || 0));
        const total = Math.max(0, subtotal - discount);
        const paid = Math.max(0, Number(paidInput.value || 0));
        const due = Math.max(0, total - paid);

        document.getElementById('summary_subtotal').textContent = formatMoney(subtotal);
        document.getElementById('summary_total').textContent = formatMoney(total);
        document.getElementById('summary_due').textContent = formatMoney(due);
    }

    function bindRowEvents(row) {
        const categorySelect = row.querySelector('.js-category');
        const productSelect = row.querySelector('.js-product');
        const qtyInput = row.querySelector('.js-qty');
        const removeBtn = row.querySelector('.js-remove-item');

        categorySelect.addEventListener('change', () => {
            updateRowProductOptions(row);
            updateRowDisplay(row);
            recalcSummary();
        });

        productSelect.addEventListener('change', () => {
            updateRowDisplay(row);
            recalcSummary();
        });

        qtyInput.addEventListener('input', () => {
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
            <td class="p-3">
                <select class="w-full js-product" required></select>
            </td>
            <td class="p-3 js-stock">-</td>
            <td class="p-3 js-price">0.00</td>
            <td class="p-3"><input type="number" step="0.01" min="0.01" class="w-28 js-qty" value="${item.quantity || 1}" required></td>
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

        updateRowDisplay(row);
        bindRowEvents(row);
        syncRowIndexes();
        recalcSummary();
    }

    function toggleCreditValidation() {
        const isCredit = paymentType.value === 'credit' || paymentType.value === 'partial';
        const hasExisting = customerSelect.value !== '';

        customerSelect.required = false;
        newCustomerName.required = isCredit && !hasExisting;
        creditNotice.classList.toggle('hidden', !isCredit);
    }

        let scannerInstance = null;

        async function startScanner() {
            scannerPanel.classList.remove('hidden');
            scannerStatus.textContent = 'Camera start ho rahi hai...';

            if (typeof Html5Qrcode === 'undefined') {
                scannerStatus.textContent = 'Scanner library load nahi hui. Internet connection check karein.';
                return;
            }

            if (scannerInstance) {
                try {
                    await scannerInstance.stop();
                    await scannerInstance.clear();
                } catch (error) {
                    // ignore stop/clear race conditions
                }
            }

            scannerInstance = new Html5Qrcode('scanner_reader');

            const cameras = await Html5Qrcode.getCameras();
            if (!cameras || cameras.length === 0) {
                scannerStatus.textContent = 'Koi camera available nahi mila.';
                return;
            }

            const preferredCamera = cameras.find((camera) => /back|rear|environment/i.test(camera.label)) || cameras[0];

            await scannerInstance.start(
                preferredCamera.id,
                { fps: 10, qrbox: { width: 250, height: 250 } },
                async (decodedText) => {
                    scannerStatus.textContent = `Scanned: ${decodedText}`;

                    try {
                        const response = await fetch(`${barcodeLookupEndpoint}/${encodeURIComponent(decodedText)}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            const errorData = await response.json().catch(() => ({}));
                            scannerStatus.textContent = errorData.message || 'Barcode ka product nahi mila.';
                            return;
                        }

                        const product = await response.json();
                        addOrIncrementProduct(product);
                        scannerStatus.textContent = `${product.name} bill mein add ho gaya.`;

                        try {
                            await scannerInstance.stop();
                        } catch (error) {
                            // ignore
                        }
                    } catch (error) {
                        scannerStatus.textContent = 'Product lookup fail ho gaya.';
                    }
                },
                () => {}
            );
        }

        async function stopScanner() {
            if (!scannerInstance) {
                scannerPanel.classList.add('hidden');
                return;
            }

            try {
                await scannerInstance.stop();
                await scannerInstance.clear();
            } catch (error) {
                // ignore
            }

            scannerInstance = null;
            scannerPanel.classList.add('hidden');
        }

        window.startSaleScanner = startScanner;
        window.stopSaleScanner = stopScanner;

        addItemBtn.addEventListener('click', () => addRow());
        openScannerBtn.addEventListener('click', () => startScanner());
        closeScannerBtn.addEventListener('click', () => stopScanner());
        discountInput.addEventListener('input', recalcSummary);
        paidInput.addEventListener('input', recalcSummary);

        paymentType.addEventListener('change', toggleCreditValidation);
        customerSelect.addEventListener('change', toggleCreditValidation);

        if (oldItems.length > 0) {
            oldItems.forEach((item) => {
                const selectedProduct = products.find((product) => String(product.id) === String(item.product_id));
                addRow({
                    category_id: selectedProduct?.category_id,
                    product_id: item.product_id,
                    quantity: item.quantity,
                });
            });
        } else {
            addRow();
        }

        toggleCreditValidation();
    });
</script>
</div>
</div>
</x-app-layout>
