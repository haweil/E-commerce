<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'is_active'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }
    public function CategoryTranslation(array $translations)
    {
        foreach ($translations as $translation) {
            $this->translations()->create([
                'locale' => $translation['locale'],
                'name' => $translation['name']
            ]);
        }
    }
}
