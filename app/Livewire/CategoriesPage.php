<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use Livewire\Attributes\Title;

#[Title('Categories', 'eShopXpert')]
class CategoriesPage extends Component
{
    public function render()
    {
        $categories = Category::where('is_active', true)->get();
        return view('livewire.categories-page', compact('categories'));
    }
}