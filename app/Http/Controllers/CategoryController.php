<?php

namespace App\Http\Controllers;

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
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-13T10:00:00Z", description="Timestamp when the category was created"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-13T12:00:00Z", description="Timestamp when the category was last updated")
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
        return response()->json(Category::all());
    }

/**
 * @OA\Post(
 *     path="/api/categories/save",
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
 *             @OA\Property(property="id", type="integer", nullable=true, example=1, description="Category ID (for update)"),
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
 *                 @OA\Property(property="name", type="string", example="Electronics"),
 *                 @OA\Property(property="description", type="string", example="Products related to electronics"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-13T10:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-13T12:00:00Z")
 *             )
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
 *                 @OA\Property(property="name", type="array",
 *                     @OA\Items(type="string", example="The name field is required.")
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

    public function save(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'nullable',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            // Check if we are creating a new category or updating an existing one
            if (empty($request->id)) {
                $category = new Category();
            } else {
                $category = Category::findOrFail($request->id);
            }

            // Set category properties
            $category->name = $validatedData['name'];
            $category->description = $validatedData['description'];
            $category->save();

            // Return the saved category
            return response()->json(['message' => 'Category saved successfully', 'category' => $category], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error saving category: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }


    public function show($id)
    {
        $category = Category::find($id);
        return response()->json($category);
    }
  
    /**
     * @OA\Post(
     *     path="/api/category/delete",
     *     tags={"Categories"},
     *     summary="Delete a category",
     *     description="This endpoint is used to delete a category by its ID.",
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         description="Category ID to delete",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1, description="ID of the category to delete")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
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
     *                 @OA\Property(property="id", type="array",
     *                     @OA\Items(type="string", example="The selected id is invalid.")
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

 
    public function destroy(Request $request)
    {
        try {
          
            $validatedData = $request->validate([
                'id' => 'required|exists:categories,id',
            ]);

            $category = Category::findOrFail($validatedData['id']);
            $category->delete();

            return response()->json(['message' => 'Category deleted successfully'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }
}
