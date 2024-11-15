<?php

namespace App\Http\Controllers;

use App\Actions\CreateProductAction;
use App\Http\Requests\ProductFormRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Retrieve a paginated list of products",
     *     description="This endpoint returns a paginated list of products available in the system.",
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of products retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer", example=1, description="Current page number"),
     *             @OA\Property(property="data", type="array", description="List of products for the current page",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1, description="Product ID"),
     *                     @OA\Property(property="name", type="string", example="Sample Product", description="Product name"),
     *                     @OA\Property(property="description", type="string", example="This is a sample product.", description="Product description"),
     *                     @OA\Property(property="price", type="number", format="float", example=199.99, description="Product price"),
     *                     @OA\Property(property="quantity", type="integer", example=100, description="Quantity in stock"),
     *                     @OA\Property(property="category_id", type="integer", example=2, description="ID of the associated category"),
     *                     
     *                 )
     *             ),
     *             @OA\Property(property="first_page_url", type="string", example="http://example.com/api/products?page=1", description="URL for the first page"),
     *             @OA\Property(property="from", type="integer", example=1, description="Starting record number on the current page"),
     *             @OA\Property(property="last_page", type="integer", example=10, description="Total number of pages"),
     *             @OA\Property(property="last_page_url", type="string", example="http://example.com/api/products?page=10", description="URL for the last page"),
     *             @OA\Property(property="next_page_url", type="string", nullable=true, example="http://example.com/api/products?page=2", description="URL for the next page, if available"),
     *             @OA\Property(property="path", type="string", example="http://example.com/api/products", description="Base URL for the API"),
     *             @OA\Property(property="per_page", type="integer", example=15, description="Number of records per page"),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true, example=null, description="URL for the previous page, if available"),
     *             @OA\Property(property="to", type="integer", example=15, description="Ending record number on the current page"),
     *             @OA\Property(property="total", type="integer", example=150, description="Total number of records available")
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

    public function index()
    {
        return ProductResource::collection(Product::paginate());
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
     *             @OA\Property(property="id", type="integer", example=null, description="Product ID (for update)"),
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
     *             @OA\Property(property="id", type="integer", example=1, description="Product ID"),
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
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function save(ProductFormRequest $request)
    {
        return (new CreateProductAction)->handle($request);
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
        try {
            $product = Product::findOrFail($id);
            return new ProductResource($product);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', 404);
        }
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
            return $this->successResponse('Deletion successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Server Error', 500);
        }
    }
}
