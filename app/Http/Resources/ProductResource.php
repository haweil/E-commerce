<?php

// Product Resource Class
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'images' => $this->formatImages($this->images),
            'description' => $this->description,
            'base_price' => $this->base_price,
            'has_sizes' => $this->has_sizes,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'in_stock' => $this->in_stock,
            'on_sale' => $this->on_sale,
            'variations' => $this->variations->map(function ($variation) {
                return [
                    'id' => $variation->id,
                    'size' => $variation->size,
                    'price' => $variation->price,
                ];
            }),
            'translations' => $this->translations->map(function ($translation) {
                return [
                    'locale' => $translation->locale,
                    'name' => $translation->name,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    private function formatImages($images)
    {
        if (!$images) {
            return [];
        }
        return array_map(function ($image) {
            return asset("storage/{$image}");
        }, $images);
    }
}
