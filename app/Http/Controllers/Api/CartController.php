<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($this->payload($this->cartOf($request)));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        if (! $product->is_active) {
            return response()->json(['message' => 'Produk tidak tersedia.'], 422);
        }

        $cart = $this->cartOf($request);
        $item = $cart->items()->where('product_id', $product->id)->first();
        $newQty = ($item?->quantity ?? 0) + $data['quantity'];

        if ($newQty > $product->stock) {
            return response()->json([
                'message' => 'Stok tidak cukup. Tersisa ' . $product->stock . '.',
            ], 422);
        }

        if ($item) {
            $item->update(['quantity' => $newQty]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $newQty,
            ]);
        }

        return response()->json($this->payload($cart), 201);
    }

    public function update(Request $request, int $itemId)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $cart = $this->cartOf($request);
        $item = $cart->items()->with('product')->findOrFail($itemId);

        if ($data['quantity'] > $item->product->stock) {
            return response()->json([
                'message' => 'Stok tidak cukup. Tersisa ' . $item->product->stock . '.',
            ], 422);
        }

        $item->update(['quantity' => $data['quantity']]);

        return response()->json($this->payload($cart));
    }

    public function remove(Request $request, int $itemId)
    {
        $cart = $this->cartOf($request);
        $cart->items()->findOrFail($itemId)->delete();

        return response()->json($this->payload($cart));
    }

    private function cartOf(Request $request): Cart
    {
        return Cart::firstOrCreate(['user_id' => $request->user()->id]);
    }

    private function payload(Cart $cart): array
    {
        $items = $cart->items()->with('product')->get()->map(fn ($i) => [
            'id' => $i->id,
            'product_id' => $i->product_id,
            'name' => $i->product->name,
            'price' => $i->product->price,
            'image' => $i->product->image,
            'quantity' => $i->quantity,
            'stock' => $i->product->stock,
            'subtotal' => $i->product->price * $i->quantity,
        ]);

        return [
            'items' => $items->values(),
            'total' => $items->sum('subtotal'),
        ];
    }
}