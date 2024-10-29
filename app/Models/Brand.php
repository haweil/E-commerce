<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{

    protected $table = 'brands';
    protected $fillable = ['name', 'slug', 'logo', 'is_active'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function translations()
    {
        return $this->hasMany(BrandTranslation::class);
    }

    public function BrandTranslation(array $translations)
    {
        foreach ($translations as $translation) {
            $this->translations()->create([
                'locale' => $translation['locale'],
                'name' => $translation['name']
            ]);
        }
    }
}
