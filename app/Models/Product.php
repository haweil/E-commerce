<?php

namespace App\Models;

use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'Sku',
        'name',
        'slug',
        'sku',
        'images',
        'description',
        'price',
        'base_price',
        'has_sizes',
        'is_active',
        'is_featured',
        'in_stock',
        'on_sale',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function ProductTranslation(array $translations)
    {
        foreach ($translations as $translation) {
            $this->translations()->create([
                'locale' => $translation['locale'],
                'name' => $translation['name'],
            ]);
        }
    }
    // App\Models\Product.php

    public function saveVariations(array $variations)
    {
        foreach ($variations as $variation) {
            $this->variations()->create([
                'size' => $variation['size'],
                'price' => $variation['price']
            ]);
        }
    }
}