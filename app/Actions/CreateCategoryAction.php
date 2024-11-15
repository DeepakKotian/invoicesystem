<?php

namespace App\Actions;

use App\Models\Category;
use App\Http\Traits\ResponserTrait;

class CreateCategoryAction
{
    use ResponserTrait;
    public function handle($request)
    {
        try {
            if (empty($request->id)) {
                $category = new Category();
            } else {
                $category = Category::findOrFail($request->id);
            }
            $category->name = $request->name;
            $category->description = $request->description;
            $category->save();

            return $this->successResponse('Category saved successfully', $category);
        } catch (\Exception $e) {
            return $this->errorResponse('An unexpected error occurred.');
        }
    }
}
