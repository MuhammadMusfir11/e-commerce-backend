<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    private const MAX_PER_PRODUCT = 12;

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $existing = $product->images()->count();
        $incoming = count($request->file('images'));

        if ($existing + $incoming > self::MAX_PER_PRODUCT) {
            return response()->json([
                'message' => 'Maksimal ' . self::MAX_PER_PRODUCT . ' gambar per produk. Saat ini sudah ada ' . $existing . '.',
            ], 422);
        }

        $order = (int) $product->images()->max('sort_order');

        foreach ($request->file('images') as $file) {
            $product->images()->create([
                'path' => $file->store('products', 'public'),
                'sort_order' => ++$order,
            ]);
        }

        return response()->json($product->load('images')->images, 201);
    }

    public function destroy(ProductImage $image)
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(['message' => 'Gambar dihapus.']);
    }
}