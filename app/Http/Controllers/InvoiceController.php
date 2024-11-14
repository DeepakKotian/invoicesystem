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
     * Generate an invoice for the customer.
     */
    public function generateInvoice(Request $request)
    {

        $validatedData = $request->validate([
            'discount' => 'nullable|numeric|min:0', 
            'tax_rate' => 'required|numeric|min:0', 
            'customer_id'=>'required|exists:customers,id',
        ]);


        $cartItems = Cart::leftJoin('products', 'carts.product_id', '=', 'products.id')
            ->select('carts.product_id', 'carts.quantity', 'products.price', 'products.name', 'carts.customer_id')
            ->where('customer_id',$request->customer_id)
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
