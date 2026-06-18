<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-upc-scan me-2"></i>Barcode Scanner</h2></x-slot>

    <div class="page-wrap py-8">
        <div class="mx-auto max-w-3xl">
            <div class="panel p-6">
                <p class="mb-4 text-sm text-gray-600">Allow camera access and point your phone/camera at a product barcode.</p>
                <div id="reader" style="width:100%"></div>
                <div id="result" class="mt-4"></div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.7/minified/html5-qrcode.min.js"></script>
    <script>
        const resultEl = document.getElementById('result');
        const html5QrcodeScanner = new Html5Qrcode('reader');

        function onScanSuccess(decodedText, decodedResult) {
            html5QrcodeScanner.stop().then(() => {
                fetch(`/product-by-barcode/${encodeURIComponent(decodedText)}`)
                    .then(r => r.json())
                    .then(json => {
                        if (json.message) {
                            resultEl.innerHTML = `<div class="text-red-600">${json.message}</div>`;
                        } else {
                            resultEl.innerHTML = `
                                <div class="p-4 border rounded">
                                    <h3 class="font-semibold">${json.name}</h3>
                                    <p>Brand: ${json.brand ?? '-'} | Type: ${json.type}</p>
                                    <p>Unit: ${json.unit} | Stock: ${json.stock_quantity}</p>
                                    <p>Price: ${json.selling_price}</p>
                                    <a href="/products/${json.id}/edit" class="btn-primary mt-2 inline-block">Open Product</a>
                                </div>
                            `;
                        }
                    }).catch(err => {
                        resultEl.innerHTML = `<div class="text-red-600">Lookup failed</div>`;
                    });
            }).catch(err => console.error('Failed to stop scanner', err));
        }

        function onScanFailure(error) {
            // ignoring continuous scan errors
        }

        Html5Qrcode.getCameras().then(cameras => {
            const cameraId = (cameras && cameras.length) ? cameras[0].id : null;
            if (!cameraId) {
                resultEl.innerHTML = '<div class="text-red-600">No camera found</div>';
                return;
            }
            html5QrcodeScanner.start(cameraId, { fps: 10, qrbox: 250 }, onScanSuccess, onScanFailure).catch(err => {
                resultEl.innerHTML = `<div class="text-red-600">${err}</div>`;
            });
        }).catch(err => {
            resultEl.innerHTML = `<div class="text-red-600">${err}</div>`;
        });
    </script>
    @endpush
</x-app-layout>
