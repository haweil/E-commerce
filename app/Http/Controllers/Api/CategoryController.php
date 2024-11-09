<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $locale = $request->header('Accept-Language', 'en');

            $cacheKey = "categories_{$locale}";
            Cache::forget($cacheKey);
            return Cache::remember($cacheKey, 3600, function () use ($locale) {
                $categories = Category::with([
                    'translations',
                    'products' => function ($query) {
                        $query->where('is_active', true);
                    }
                ])
                    ->where('is_active', true)
                    ->get()
                    ->map(function ($category) use ($locale) {
                        // Get translations for all supported languages
                        $translations = collect($category->translations)
                            ->mapWithKeys(function ($translation) {
                                return [$translation->locale => $translation->name];
                            })
                            ->toArray();
                        return [
                            'id' => $category->id,
                            'slug' => $category->slug,
                            'image' => asset("storage{$category->folder}/{$category->image}"),
                            'name' => $translations[$locale] ?? $category->name,
                            'translations' => $translations,
                            'products_count' => $category->products->count()
                        ];
                    });

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'categories' => $categories,
                        'locale' => $locale
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
