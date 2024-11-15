<?php

namespace App\Actions;

use App\Models\Category;
use App\Http\Traits\ResponserTrait;

class UpdateCategoryAction
{
    use ResponserTrait;
    
    public function handle($request)
    {
        try {
            $category = Category::findOrFail($request->id);
            $category->name = $request->name;
            $category->description = $request->description;
            $category->save();

            return $this->successResponse('Category updated successfully', $category);
        } catch (\Exception $e) {
            return $this->errorResponse('An unexpected error occurred.');
        }
    }
}
