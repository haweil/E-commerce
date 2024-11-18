<?php

namespace App\Http\Controllers\API;

use DB;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
        ]);

        $user = $request->user();

        if ($request->variation_id) {
            $variation = DB::table('product_variations')->where('id', $request->variation_id)->first();
            if ($variation->product_id != $request->product_id) {
                return response()->json(['message' => 'Variation not found'], 404);
            }
        }
        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->where('variation_id', $request->variation_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += 1;
            $cartItem->save();
            $cart = $cartItem;
        } else {
            $cart = Cart::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'variation_id' => $request->variation_id,
                'quantity' => 1, // Default to 1
            ]);
        }

        return response()->json(['message' => 'Product added to cart successfully', 'cart' => $cart]);
    }


    public function subtractFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
        ]);

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->where('variation_id', $request->variation_id)
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Product not found in cart'], 404);
        }
        if ($cart->quantity <= 1) {
            $cart?->delete();
            return response()->json(['message' => 'Product removed from cart']);
        }

        $cart->decrement('quantity');
        return response()->json(['message' => 'Product quantity decreased', 'cart' => $cart]);
    }

    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
        ]);

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->where('variation_id', $request->variation_id)
            ->first();

        if ($cart) {
            $cart->delete();
            return response()->json(['message' => 'Product removed from cart']);
        }

        return response()->json(['message' => 'Product not found in cart'], 404);
    }

    public function getCart(Request $request)
    {
        $user = $request->user();
        $locale = $request->header('Accept-Language', 'en');
        $cartItems = Cart::with(['product.variations', 'product.translations'])->where('user_id', $user->id)->get();

        $formattedCart = $cartItems->map(function ($cart) use ($locale) {
            $product = $cart->product;
            $variation = $product->variations->firstWhere('id', $cart->variation_id); // Assuming cart stores `variation_id`

            // Get product name translation
            $productTranslations = $product->translations->pluck('name', 'locale');
            $productName = $productTranslations[$locale] ?? $product->name;

            $price = $variation ? $variation->price : $product->base_price;

            return [
                'product_id' => $product->id,
                'name' => $productName,
                'size' => $variation?->size ?? 'Default',
                'quantity' => $cart->quantity,
                'price' => $price,
                'total_price' => $cart->quantity * $price,
                'image' => $product->images ? asset('storage/' . $product->images[0]) : null,
            ];
        });

        return response()->json(['cart' => $formattedCart]);
    }
}