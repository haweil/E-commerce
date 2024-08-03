<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Title;


#[Title('Product Detail - eShopXpert')]
class ProductDetailPage extends Component
{

    public $slug;
    public function mount($slug)
    {
        $this->slug = $slug;
    }
    public function render()
    {
        $product = Product::where('slug', $this->slug)->firstOrFail();
        return view('livewire.product-detail-page', compact('product'));
    }
}
