<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminOrderController extends Controller
{
    private const STATUSES = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
    private const PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded'];

    public function index(Request $request)
    {
        $orders = Order::with('user:id,name,email')
            ->withCount('items')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function show(int $id)
    {
        return response()->json(
            Order::with(['items', 'user:id,name,email'])->findOrFail($id)
        );
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => ['sometimes', 'in:' . implode(',', self::STATUSES)],
            'payment_status' => ['sometimes', 'in:' . implode(',', self::PAYMENT_STATUSES)],
        ]);

        $order = DB::transaction(function () use ($id, $data) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($id);

            // Pesanan yang sudah dibatalkan tidak boleh dibuka lagi
            if ($order->status === 'cancelled' && isset($data['status']) && $data['status'] !== 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => ['Pesanan yang sudah dibatalkan tidak bisa diubah lagi.'],
                ]);
            }

            // Saat dibatalkan, stok dikembalikan
            if (($data['status'] ?? null) === 'cancelled' && $order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update($data);

            return $order;
        });

        return response()->json($order->load('items'));
    }
}