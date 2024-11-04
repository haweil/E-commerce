<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Order\OrderController;



Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::post('/orders', [OrderController::class, 'StripeCheckout'])->name('order.stripe')->middleware('auth:sanctum');
Route::get('/orders/success/{order_id}', [OrderController::class, 'StripeCheckoutSuccess'])->name('order.success');
Route::get('/orders/cancel', [OrderController::class, 'StripeCheckoutCancel'])->name('order.cancel');
