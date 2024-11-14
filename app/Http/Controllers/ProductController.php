<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFormRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ProductController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/products",
 *     tags={"Products"},
 *     summary="Retrieve a list of all products",
 *     description="This endpoint returns a list of all products available in the system.",
 *     
 *     @OA\Response(
 *         response=200,
 *         description="List of products retrieved successfully",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1, description="Product ID"),
 *                 @OA\Property(property="name", type="string", example="Sample Product", description="Product name"),
 *                 @OA\Property(property="description", type="string", example="This is a sample product.", description="Product description"),
 *                 @OA\Property(property="price", type="number", format="float", example=199.99, description="Product price"),
 *                 @OA\Property(property="quantity", type="integer", example=100, description="Quantity in stock"),
 *                 @OA\Property(property="category_id", type="integer", example=2, description="ID of the associated category"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-13T10:00:00Z", description="Timestamp when the product was created"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-13T12:00:00Z", description="Timestamp when the product was last updated")
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
 *             @OA\Property(property="message", type="string", example="An unexpected error occurred while retrieving the products.")
 *         )
 *     )
 * )
 */
    public function index()
    {
        return response()->json(Product::all());
    }

    /**
     * @OA\Post(
     *     path="/api/product/save",
     *     tags={"Products"},
     *     summary="Add a new product or update an existing product",
     *     description="This endpoint is used to create a new product or update an existing product based on the provided product ID.",
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         description="Product data to create or update",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "description", "price", "quantity", "category_id"},
     *             @OA\Property(property="id", type="integer", example=1, description="Product ID (for update)"),
     *             @OA\Property(property="name", type="string", example="Sample Product", description="Product name"),
     *             @OA\Property(property="description", type="string", example="This is a sample product.", description="Product description"),
     *             @OA\Property(property="price", type="number", format="float", example=199.99, description="Product price"),
     *             @OA\Property(property="quantity", type="integer", example=100, description="Quantity in stock"),
     *             @OA\Property(property="category_id", type="integer", example=2, description="ID of the product category"),
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=201,
     *         description="Product created or updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=null, description="Product ID"),
     *             @OA\Property(property="name", type="string", example="Sample Product"),
     *             @OA\Property(property="description", type="string", example="This is a sample product."),
     *             @OA\Property(property="price", type="number", format="float", example=199.99),
     *             @OA\Property(property="quantity", type="integer", example=100),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-13T10:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-13T10:00:00Z")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Bad Request"),
     *             @OA\Property(property="message", type="string", example="Validation failed for the provided product data.")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Server Error"),
     *             @OA\Property(property="message", type="string", example="Internal server error occurred while saving the product.")
     *         )
     *     )
     * )
     */
    public function save(ProductFormRequest $request)
    {
        try {
            // If the 'id' is provided, try to find the existing product.
            if (!empty($request->id) && isset($request->id)) {
                $product = Product::find($request->id);

                // If product not found, return an error message
                if (!$product) {
                    return response()->json([
                        'error' => 'Product not found',
                        'message' => 'No product found with the given ID for update.'
                    ], 404);
                }
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

            // Save the product
            $product->save();

            // Return a successful response with the product data
            return response()->json($product, 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/product/view/{id}",
     *     tags={"Products"},
     *     summary="Get a product by ID",
     *     description="Retrieve a product's details by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Sample Product"),
     *             @OA\Property(property="description", type="string", example="This is a sample product."),
     *             @OA\Property(property="price", type="number", format="float", example=199.99),
     *             @OA\Property(property="quantity", type="integer", example=100),
     *             @OA\Property(property="category_id", type="integer", example=2),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-04T06:13:12.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-04T06:13:12.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Product not found"),
     *             @OA\Property(property="message", type="string", example="No product found with the given ID.")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'error' => 'Product not found',
                'message' => 'No product found with the given ID.'
            ], 404);
        }

        return response()->json($product);
    }


    /**
     * @OA\Post(
     *     path="/api/product/delete",
     *     tags={"Products"},
     *     summary="Delete a product by ID",
     *     description="Delete a product based on the provided ID.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="The ID of the product to delete",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             type="string",
     *             example="Deletion successful"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Product not found"),
     *             @OA\Property(property="message", type="string", example="No product found with the given ID.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Server Error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:products,id',
            ]);
            $checkProduct = Product::find($request->id);
            $checkProduct->delete();
            return response()->json('Deletion successful', 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => 'Something went wrong'
            ], 500);
        }
    }
}
