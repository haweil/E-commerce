<?php

namespace App\Models;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Model;

class BrandTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'locale', 'brand_id'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}