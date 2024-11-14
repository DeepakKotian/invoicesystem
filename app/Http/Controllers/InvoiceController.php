<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/invoice/generate",
     *     tags={"Invoice"},
     *     summary="Generate an invoice from the customer's cart",
     *     description="This endpoint generates an invoice for the customer based on the products in their cart. It calculates the subtotal, applies any discount, adds tax, and returns the total amount for the invoice.",
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         description="Invoice data to generate, including customer ID, tax rate, and optional discount",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"tax_rate", "customer_id"},
     *             @OA\Property(property="discount", type="number", format="float", example=10.0, description="Discount applied to the total amount (optional)"),
     *             @OA\Property(property="tax_rate", type="number", format="float", example=15.0, description="Tax rate to apply to the subtotal (in percentage)"),
     *             @OA\Property(property="customer_id", type="integer", example=123, description="ID of the customer whose cart will be used for the invoice")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=201,
     *         description="Invoice generated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invoice generated successfully"),
     *             @OA\Property(property="invoice", type="object",
     *                 @OA\Property(property="id", type="integer", example=1, description="Invoice ID"),
     *                 @OA\Property(property="customer_name", type="string", example="John Doe", description="Customer's name"),
     *                 @OA\Property(property="customer_email", type="string", example="johndoe@example.com", description="Customer's email"),
     *                 @OA\Property(property="customer_address", type="string", example="1234 Elm St.", description="Customer's address"),
     *                 @OA\Property(property="subtotal", type="number", format="float", example=100.00, description="Subtotal of the cart items"),
     *                 @OA\Property(property="discount", type="number", format="float", example=10.00, description="Discount applied"),
     *                 @OA\Property(property="tax_rate", type="number", format="float", example=15.0, description="Tax rate applied"),
     *                 @OA\Property(property="tax_amount", type="number", format="float", example=13.50, description="Tax amount calculated"),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=103.50, description="Total amount after discount and tax")
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Cart is empty or missing required parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Cart is empty"),
     *             @OA\Property(property="message", type="string", example="No products in cart to generate invoice.")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Customer not found"),
     *             @OA\Property(property="message", type="string", example="The customer associated with this cart could not be found.")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Validation Error"),
     *             @OA\Property(property="messages", type="object", additionalProperties={"type":"array","items":{"type":"string"}})
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Server Error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while generating the invoice.")
     *         )
     *     )
     * )
     */

    public function generateInvoice(Request $request)
    {

        $validatedData = $request->validate([
            'discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'customer_id' => 'required|exists:customers,id',
        ]);


        $cartItems = Cart::leftJoin('products', 'carts.product_id', '=', 'products.id')
            ->select('carts.product_id', 'carts.quantity', 'products.price', 'products.name', 'carts.customer_id')
            ->where('customer_id', $request->customer_id)
            ->get();


        if ($cartItems->isEmpty()) {
            return response()->json([
                'error' => 'Cart is empty',
                'message' => 'No products in cart to generate invoice.'
            ], 400);
        }


        $customerId = $request->customer_id;
        $customer = Customer::find($request->customer_id);


        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found',
                'message' => 'The customer associated with this cart could not be found.'
            ], 404);
        }


        $subtotal = 0;
        foreach ($cartItems as $item) {
            $productPrice = $item->price * $item->quantity;
            $subtotal += $productPrice;
        }


        $discountAmount = $validatedData['discount'] ?? 0;
        $subtotalAfterDiscount = $subtotal - $discountAmount;


        $taxAmount = ($validatedData['tax_rate'] / 100) * $subtotalAfterDiscount;


        $totalAmount = $subtotalAfterDiscount + $taxAmount;

        $invoice = new Invoice();
        $invoice->customer_name = $customer->name;
        $invoice->customer_id = $customer->id;
        $invoice->customer_email = $customer->email;
        $invoice->customer_address = $customer->address;
        $invoice->subtotal = $subtotal;
        $invoice->discount = $discountAmount;
        $invoice->tax_rate = $validatedData['tax_rate'];
        $invoice->tax_amount = $taxAmount;
        $invoice->total_amount = $totalAmount;
        $invoice->save();



        Cart::where('customer_id', $customerId)->delete();

        return response()->json([
            'message' => 'Invoice generated successfully',
            'invoice' => $invoice
        ], 201);
    }
}
