<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{

    public function addToCart(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            if ($product->quantity < $request->quantity) {
                return response()->json([
                    'error' => 'No Stock',
                    'message' => 'Out of Stock.'
                ], 400);
            }
            $cart = Cart::where('product_id', $request->product_id)->first();
            if ($cart) {
                $cart->quantity += $request->quantity;
                $cart->save();
            } else {
                $cart = new Cart();
                $cart->product_id = $request->product_id;
                $cart->quantity = $request->quantity;
                $cart->customer_id = $request->customer_id;
                $cart->save();
            }

            return response()->json([
                'message' => 'Product added to cart successfully',
                'cart' => $cart
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function viewCart()
    {
        try {
            $cartItems = Cart::select('products.id as productId','customer_id','products.name','products.id','carts.id as cartId','carts.quantity')->leftJoin('products','products.id','carts.product_id')->get(); 

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'message' => 'Your cart is empty.'
                ], 404);
            }

            return response()->json([
                'cart_items' => $cartItems
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function removeFromCart(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:carts,product_id'
        ]);

        try {
            $cart = Cart::where('product_id', $validatedData['product_id'])->first();
            if ($cart) {
                $cart->delete();
                return response()->json([
                    'message' => 'Product removed from cart successfully'
                ], 200);
            }

            return response()->json([
                'error' => 'Product Not Found in Cart',
                'message' => 'The specified product is not in your cart.'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


   
}
