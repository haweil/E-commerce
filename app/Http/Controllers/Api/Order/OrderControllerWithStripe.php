<?php

namespace App\Http\Controllers\Api\Order;

use Stripe\Stripe;
use App\Models\Order;
use App\Models\Address;
use App\Models\OrderItem;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session as CheckoutSession;

class OrderControllerWithStripe extends Controller
{
    public function StripeCheckout(Request $request)
    {

        $request->validate([
            'items' => 'required|array',
            'address' => 'required|array',
            'shipping_amount' => 'required|numeric',
            'discount_code' => 'nullable|string',
        ]);
        $subtotal = $this->calculateSubtotal($request->items);
        $discountDetails = $this->handleDiscount($request->discount_code, $subtotal);

        $grandTotal = $subtotal + $request->shipping_amount - $discountDetails['amount'];

        $order = Order::create([
            'user_id' => Auth::id(),
            'grand_total' => $grandTotal,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'status' => 'new',
            'currency' => 'eur',
            'shipping_amount' => $request->shipping_amount,
            'shipping_method' => '1',
            'notes' => $request->notes ?? null,
        ]);

        foreach ($request->items as $item) {
            OrderItem::create([
                'product_id' => $item['product_id'],
                'order_id' => $order->id,
                'quantity' => $item['quantity'],
                'variation_id' => $item['variation_id'] ?? null,
                'unit_amount' => $item['unit_amount'],
                'total_amount' => $item['total_amount'],
            ]);
        }

        $address = Address::create(array_merge($request->address, ['order_id' => $order->id]));

        // Initialize Stripe
        Stripe::setApiKey(config('services.stripe.secret'));
        // Create Checkout Session
        $lineItems = $this->prepareLineItems($request->items);




        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'shipping_options' => [
                [
                    'shipping_rate_data' => [
                        'type' => 'fixed_amount',
                        'fixed_amount' => [
                            'amount' => (int)($request->shipping_amount * 100),
                            'currency' => 'eur',
                        ],
                        'display_name' => 'Standard Shipping',
                        'delivery_estimate' => [
                            'minimum' => ['unit' => 'business_day', 'value' => 3],
                            'maximum' => ['unit' => 'business_day', 'value' => 5],
                        ],
                    ],
                ],
            ],
            'mode' => 'payment',

            'success_url' => route('order.success', ['order_id' => $order->id]),
            'cancel_url' => route('order.cancel'),
            'metadata' => [
                'order_id' => $order->id,
                'subtotal' => $subtotal,
                'shipping' => $request->shipping_amount,
                'discount' => $discountDetails['amount'],
            ],
        ];


        if ($discountDetails['amount'] > 0) {
            $couponId = 'COUPON_' . uniqid();
            try {
                $coupon = \Stripe\Coupon::create([
                    'id' => $couponId,
                    'amount_off' => (int)($discountDetails['amount'] * 100),
                    'currency' => 'eur',
                    'name' => $discountDetails['code'],
                    'duration' => 'forever',
                ]);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Handle the error accordingly
                return response()->json(['error' => 'Failed to create coupon: ' . $e->getMessage()], 500);
            }
        }
        if ($discountDetails['amount'] > 0) {
            $sessionParams['discounts'] = [
                [
                    'coupon' => $couponId,
                ],
            ];
        }
        $session = Session::create($sessionParams);
        $order->update([
            'session_stripe_id' => $session->id,
        ]);
        return response()->json($session->url);
    }

    public function StripeCheckoutSuccess($order_id)
    {
        // Find the order
        $order = Order::find($order_id);

        if ($order) {
            // Retrieve session info from Stripe
            Stripe::setApiKey(config('services.stripe.secret'));

            try {
                $session = Session::retrieve($order->session_stripe_id);

                // Check the payment status
                if ($session->payment_status == 'paid') {
                    // Update order status to processing and payment status to paid
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'processing',
                    ]);

                    return response()->json(['message' => 'Payment successful', 'order' => $order], 200);
                } else {
                    $this->handleFailedPayment($order);
                    return response()->json(['message' => 'Payment not successful. Please try again.'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['message' => 'Error retrieving session: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => 'Order not found'], 404);
    }

    public function StripeCheckoutCancel()
    {

        return response()->json(['message' => 'Payment was canceled. Please try again.'], 200);
    }

    private function calculateGrandTotal($items, $shippingAmount)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['unit_amount'] * $item['quantity'];
        }
        return $total + $shippingAmount;
    }
    private function prepareLineItems(array $items): array
    {
        return array_map(function ($item) {
            return [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['name'],
                        'images' => isset($item['image_url']) ? [$item['image_url']] : [],
                        'metadata' => [
                            'product_id' => $item['product_id'],
                        ],
                    ],
                    'unit_amount' => (int)($item['unit_amount'] * 100),
                ],
                'quantity' => $item['quantity'],
            ];
        }, $items);
    }
    private function handleFailedPayment(Order $order)
    {
        $order->update([
            'payment_status' => 'failed',
            'status' => 'cancelled',
        ]);
    }

    private function calculateSubtotal($items)
    {
        return array_reduce($items, function ($carry, $item) {
            return $carry + ($item['unit_amount'] * $item['quantity']);
        }, 0);
    }

    private function calculateDiscountAmount($subtotal, DiscountCode $discount)
    {
        if ($discount->discount_percentage) {
            return $subtotal * ($discount->discount_percentage / 100);
        }

        return min($discount->discount_amount, $subtotal);
    }
    private function handleDiscount(?string $code, float $subtotal): array
    {
        if (!$code) {
            return ['amount' => 0, 'discount_id' => null];
        }

        $discount = DiscountCode::where('code', $code)->first();

        if (!$discount || !$discount->isValid() || $subtotal < $discount->minimum_order_value) {
            return ['amount' => 0, 'discount_id' => null];
        }

        $discountAmount = $this->calculateDiscountAmount($subtotal, $discount);
        return [
            'amount' => $discountAmount,
            'discount_id' => $discount->id,
            'code' => $discount->code
        ];
    }
}
