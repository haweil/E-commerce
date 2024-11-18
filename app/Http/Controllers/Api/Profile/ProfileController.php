<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = auth()->user();

        // Validate request
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'current_password' => 'required_with:new_password|current_password',
            'new_password' => 'sometimes|string|min:6|confirmed',
        ]);

        try {
            // Update basic info
            $updateData = [];

            if ($request->has('name')) {
                $updateData['name'] = $validatedData['name'];
            }

            if ($request->has('email')) {
                $updateData['email'] = $validatedData['email'];
            }

            // Update password if provided
            if ($request->has('new_password')) {
                $updateData['password'] = Hash::make($validatedData['new_password']);
            }

            if (empty($updateData)) {
                return response()->json([
                    'message' => 'Nothing to update'
                ]);
            }
            $user->update($updateData);

            // Return updated user data (excluding sensitive fields)
            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request)
    {
        $user = auth()->user();
        // return order data
        $user = $user->with('orders')->find($user->id);
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                // 'orders' => $user->orders
            ]
        ]);
    }
}
