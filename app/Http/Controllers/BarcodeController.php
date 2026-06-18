<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Facades\Response;

class BarcodeController extends Controller
{
    public function image(Product $product)
    {
        // Generate Code128 by default (suits most uses). For EAN-13 you need 13-digit numeric barcodes.
        $generator = new BarcodeGeneratorPNG();
        $code = $product->barcode ?? (string) $product->id;

        $barcodeData = $generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 60);

        return Response::make($barcodeData, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="barcode.png"'
        ]);
    }

    public function scanner()
    {
        return view('products.scanner');
    }

    public function lookup($code)
    {
        $product = Product::where('barcode', $code)->first();
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }
}
