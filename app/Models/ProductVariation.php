<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariation extends Model
{
    protected $fillable = ['product_id', 'size', 'price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
