<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    /**
     * Get products list with optional filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variations', 'translations']);

        // Apply category filter
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Apply price filter
        if ($request->has('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        // Apply active filter
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Apply stock filter
        if ($request->has('in_stock')) {
            $query->where('in_stock', $request->in_stock);
        }

        // Apply featured filter
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->is_featured);
        }

        // Apply sale filter
        if ($request->has('on_sale')) {
            $query->where('on_sale', $request->on_sale);
        }


        // Apply pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products),
            'meta' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ]
        ]);
    }

    /**
     * Get single product by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {

        $product = Product::with(['category', 'variations', 'translations'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => new ProductResource($product)
        ]);
    }
}