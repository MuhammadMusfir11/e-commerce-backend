<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id', 'name', 'slug', 'description',
    'price', 'old_price', 'stock', 'image', 'is_active', 'is_featured',
])]
class Product extends Model
{
    use HasFactory;

    protected $appends = ['cover'];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'old_price' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    // Gambar utama: gambar upload pertama, atau kolom image lama jika belum ada upload
    protected function cover(): Attribute
    {
        return Attribute::get(function () {
            if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
                return $this->images->first()->url;
            }

            return $this->image;
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }
}