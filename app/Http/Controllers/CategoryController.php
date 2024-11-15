<?php

namespace App\Http\Controllers;

use App\Actions\CreateCategoryAction;
use App\Actions\UpdateCategoryAction;
use App\Http\Requests\CategoryFormRequest;
use App\Http\Requests\CategoryUpdateFormRequest;
use App\Http\Requests\DeleteCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Retrieve a list of all categories",
     *     description="This endpoint returns a list of all categories available in the system.",
     *     
     *     @OA\Response(
     *         response=200,
     *         description="List of categories retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1, description="Category ID"),
     *                 @OA\Property(property="name", type="string", example="Electronics", description="Category name"),
     *                 @OA\Property(property="description", type="string", example="Products related to electronics", description="Category description"),
     *        
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
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while retrieving the categories.")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return CategoryResource::collection(Category::all());
    }

    /**
     * @OA\Post(
     *     path="/api/category/save",
     *     tags={"Categories"},
     *     summary="Save or update a category",
     *     description="This endpoint is used to create a new category or update an existing one based on the provided ID.",
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         description="Category data to create or update",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="id", type="integer", nullable=true, example=null, description="Category ID (for update)"),
     *             @OA\Property(property="name", type="string", example="Electronics", description="Category name"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Products related to electronics", description="Category description")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=201,
     *         description="Category saved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Category saved successfully"),
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Category name"),
     *                 @OA\Property(property="description", type="string", example="Category Description"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-13T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-13T12:00:00Z")
     *             )
     *         )
     *     ),
     *     
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred.")
     *         )
     *     )
     * )
     */

    public function save(CategoryFormRequest $request)
    {
        return (new CreateCategoryAction)->handle($request);
    }

    /**
     * @OA\Put(
     *     path="/api/category/update",
     *     operationId="updateCategory",
     *     tags={"Categories"},
     *     summary="Update an existing category",
     *     description="This endpoint updates the details of a specific category by ID.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"id", "name"},
     *             @OA\Property(property="id", type="integer", example=1, description="ID of the category to update"),
     *             @OA\Property(property="name", type="string", example="Updated Category", description="New name of the category"),
     *             @OA\Property(property="description", type="string", example="Updated description", description="New description of the category")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Category updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Updated Category"),
     *                 @OA\Property(property="description", type="string", example="Updated description")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An unexpected error occurred.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred.")
     *         )
     *     )
     * )
     */
    public function update(CategoryUpdateFormRequest $request)
    {
        return (new UpdateCategoryAction)->handle($request);
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->errorResponse('No category found with the given ID.');
        }
        return new CategoryResource($category);
    }

    /**
     * @OA\Delete(
     *     path="/api/category/delete/{id}",
     *     tags={"Categories"},
     *     summary="Delete a category",
     *     description="Deletes a category by its ID. If the category does not exist, a 404 Not Found response is returned.",
     *     
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the category to delete",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category deleted successfully"
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Not Found"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category not found"
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Server Error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="An unexpected error occurred."
     *             )
     *         )
     *     )
     * )
     */


    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id); 
            $category->delete(); 

            return response()->json([
                'message' => 'Category deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle category not found
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            // Handle unexpected server errors
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }
}
