<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/cart/add",
     *     tags={"Cart"},
     *     summary="Add a product to the cart",
     *     description="This endpoint adds a product to the user's cart. If the product is already in the cart, it increases the quantity. If the stock is insufficient, it returns an error.",
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data for adding a product to the cart",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"product_id", "quantity", "customer_id"},
     *             @OA\Property(property="product_id", type="integer", example=1, description="ID of the product to add to the cart"),
     *             @OA\Property(property="quantity", type="integer", example=2, description="Quantity of the product to add"),
     *             @OA\Property(property="customer_id", type="integer", example=1, description="ID of the customer")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=201,
     *         description="Product added to cart successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Product added to cart successfully"),
     *             @OA\Property(property="cart", type="object",
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=3),
     *                 @OA\Property(property="customer_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-13T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-13T10:05:00Z")
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Insufficient Stock",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="No Stock"),
     *             @OA\Property(property="message", type="string", example="Out of Stock.")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Validation Error"),
     *             @OA\Property(property="messages", type="object",
     *                 @OA\Property(property="product_id", type="array", 
     *                     @OA\Items(type="string", example="The selected product_id is invalid.")
     *                 ),
     *                 @OA\Property(property="quantity", type="array", 
     *                     @OA\Items(type="string", example="The quantity field must be an integer.")
     *                 )
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Server Error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred.")
     *         )
     *     )
     * )
     */

    public function addToCart(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'customer_id' => 'required|exists:customers,id',
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            if ($product->quantity < $request->quantity) {
                return $this->errorResponse('Product Out of Stock.');
            }
            $cart = Cart::where('product_id', $request->product_id)->where('customer_id',$request->customer_id)->first();
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

      
            return $this->successResponse('Product added to cart successfully',$cart);
        } catch (\Exception $e) {
           return $this->errorResponse('Server Error', $e->getMessage());
        }
    }
    /**
     * @OA\Get(
     *     path="/api/cart/view",
     *     tags={"Cart"},
     *     summary="View all items in the cart",
     *     description="This endpoint returns all the products added to the customer's cart. If the cart is empty, a 404 response is returned.",
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved cart items",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="cart_items", type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="productId", type="integer", example=1, description="ID of the product in the cart"),
     *                     @OA\Property(property="customer_id", type="integer", example=1, description="ID of the customer"),
     *                     @OA\Property(property="name", type="string", example="Sample Product", description="Product name"),
     *                     @OA\Property(property="id", type="integer", example=1, description="Product ID"),
     *                     @OA\Property(property="cartId", type="integer", example=10, description="Cart item ID"),
     *                     @OA\Property(property="quantity", type="integer", example=2, description="Quantity of the product in the cart")
     *                 )
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=404,
     *         description="Cart is empty",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Your cart is empty.")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Server Error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred.")
     *         )
     *     )
     * )
     */

    public function viewCart()
    {
        try {
            $cartItems = Cart::select('products.id as productId', 'customer_id', 'products.name', 'products.id', 'carts.id as cartId', 'carts.quantity')
                ->leftJoin('products', 'products.id', 'carts.product_id')->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'message' => 'Your cart is empty.'
                ], 404);
            }

            return response()->json([
                'data' => $cartItems
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
            'product_id' => 'required|exists:carts,product_id',
            'customer_id' => 'required|exists:customers,id',
        ]);

        try {
            $cart = Cart::where('product_id', $validatedData['product_id'])->where('customer_id', $validatedData['customer_id'])->first();
            if ($cart) {
                $cart->delete();
                return $this->successResponse('Product removed from cart successfully');
            }

            return $this->errorResponse('The specified product is not in your cart.');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Server Error');
        }
    }
}
