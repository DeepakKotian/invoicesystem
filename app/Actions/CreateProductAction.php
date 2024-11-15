<?php

namespace App\Actions;

use App\Models\Product;
use App\Http\Traits\ResponserTrait;

class CreateProductAction
{
    use ResponserTrait;
    public function handle($request)
    {
        try {
            // If the 'id' is provided, try to find the existing product.
            if (!empty($request->id) && isset($request->id)) {
                $product = Product::find($request->id);
            } else {
                // If 'id' is not provided, create a new product
                $product = new Product();
            }
            // Assign values to product fields
            $product->name = $request->name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->quantity = $request->quantity;
            $product->category_id = $request->category_id;
            $product->save();

            // Return a successful response with the product data
            return $this->successResponse('Saved successfully', $product,201);
        } catch (\Exception $e) {
            return $this->errorResponse('Server Error', 500);
        }
    }
}
