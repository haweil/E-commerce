<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use App\Models\Order;
use App\Models\Address;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session as CheckoutSession;

class OrderController extends Controller
{
    public function StripeCheckout(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'address' => 'required|array',
            'shipping_amount' => 'required|numeric',
        ]);

        $order = Order::create([
            'user_id' => Auth::id(),
            'grand_total' => $this->calculateGrandTotal($request->items, $request->shipping_amount),
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'status' => 'new',
            'currency' => 'EUR',
            'shipping_amount' => $request->shipping_amount,
            'shipping_method' => 'standard',
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
        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $this->prepareLineItems($request->items),
            'mode' => 'payment',
            'success_url' => route('', ['order_id' => $order->id]),
            'cancel_url' => route('order.cancel'),
            'metadata' => ['order_id' => $order->id],
        ]);
        $order->update(['session_stripe_id' => $session->id]);

        return response()->json(['id' => $session->id]);
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
                    // Update order status to completed
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
    private function prepareLineItems($items)
    {
        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['name'],
                        'images' => [$item['image_url'] ?? ''],
                    ],
                    'unit_amount' => $item['unit_amount'] * 100,
                ],
                'quantity' => $item['quantity'],
            ];
        }
        return $lineItems;
    }
    private function handleFailedPayment(Order $order)
    {
        // If the payment failed, update the order status
        $order->update([
            'payment_status' => 'failed',
            'status' => 'cancelled',
        ]);

        // For example, notify the user about the failed payment
    }
}
