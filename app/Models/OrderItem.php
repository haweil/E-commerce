<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'order_id',
        'quantity',
        'variation_id',
        'Size',
        'unit_amount',
        'total_amount',
    ];

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}