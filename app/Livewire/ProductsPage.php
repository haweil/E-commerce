<?php

namespace App\Livewire;

use App\Models\Brand;
use Livewire\Component;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Title('Products - eShopXpert')]
class ProductsPage extends Component
{

    use WithPagination;

    #[Url]
    public $selected_Categories = [];

    #[Url]
    public $selected_Brands = [];

    #[Url]
    public $featured;

    #[Url]
    public $onSale;

    #[Url]
    public $price_range = 100;
    public function render()
    {
        $productQuery = Product::query()->where('is_active', 1);


        if (!empty($this->selected_Categories)) {
            $productQuery->whereIn('category_id', $this->selected_Categories);
        }

        if (!empty($this->selected_Brands)) {
            $productQuery->whereIn('brand_id', $this->selected_Brands);
        }

        if ($this->featured) {
            $productQuery->where('is_featured', 1);
        }

        if ($this->onSale) {
            $productQuery->where('on_sale', 1);
        }

        if ($this->price_range) {
            $productQuery->whereBetween('price', [0, $this->price_range]);
        }
        $categories = Category::where('is_active', true)->get(['id', 'name', 'slug']);
        $brands = Brand::where('is_active', true)->get(['id', 'name', 'slug']);
        return view('livewire.products-page', [
            'categories' => $categories,
            'brands' => $brands,
            'products' => $productQuery->paginate(9),
        ]);
    }
}