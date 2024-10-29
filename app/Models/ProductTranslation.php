<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['product_id', 'locale', 'name'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
