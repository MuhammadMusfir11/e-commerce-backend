<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'images'])
            ->where('is_active', true)
            ->when($request->query('category'), function ($query, $slug) {
                $query->whereHas('category', fn ($c) => $c->where('slug', $slug));
            })
            ->when($request->query('search'), function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('id')
            ->get();

        return response()->json($products);
    }

    public function show(Product $product)
    {
        abort_if(! $product->is_active, 404);

        return response()->json($product->load(['category', 'images']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'old_price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:2048'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);

        $data['slug'] = $this->makeSlug($data['name']);

        $product = Product::create($data);

        return response()->json($product->load(['category', 'images']), 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'integer', 'min:0'],
            'old_price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:2048'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);

        if (isset($data['name'])) {
            $data['slug'] = $this->makeSlug($data['name'], $product->id);
        }

        $product->update($data);

        return response()->json($product->load(['category', 'images']));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Produk dihapus.']);
    }

    private function makeSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}