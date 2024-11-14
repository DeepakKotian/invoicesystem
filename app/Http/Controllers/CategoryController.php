<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
  
    public function index()
    {
        return response()->json(Category::all());
    }


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
