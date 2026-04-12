<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = Category::query()->latest('id')->get();

        return response()->json([
            'status' => 'success',
            'message' => $categories->isEmpty()
                ? 'No categories found'
                : 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Category retrieved successfully',
            'data' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        if ($validated === []) {
            return response()->json([
                'status' => 'error',
                'message' => 'No update data was provided',
            ], 422);
        }

        $category->fill($validated);

        if (!$category->isDirty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No changes detected',
                'data' => $category,
            ]);
        }

        $category->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
            'data' => $category->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully',
        ]);
    }
}
