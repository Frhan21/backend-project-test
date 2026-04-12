<?php

namespace App\Http\Controllers;

use App\Models\Outcome;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OutcomeController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $outcomes = Outcome::query()
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => $outcomes->isEmpty()
                ? 'No outcomes found'
                : 'Outcomes retrieved successfully',
            'data' => $outcomes,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'total' => ['required', 'numeric', 'min:0'],
            'outcome_date' => ['required', 'date'],
        ]);

        $outcome = Outcome::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Outcome created successfully',
            'data' => $outcome,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Outcome $outcome): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Outcome retrieved successfully',
            'data' => $outcome,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Outcome $outcome): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['sometimes', 'required', 'string'],
            'category_id' => ['sometimes', 'required', 'integer', Rule::exists('categories', 'id')],
            'total' => ['sometimes', 'required', 'numeric', 'min:0'],
            'outcome_date' => ['sometimes', 'required', 'date'],
        ]);

        if ($validated === []) {
            return response()->json([
                'status' => 'error',
                'message' => 'No update data was provided',
            ], 422);
        }

        $outcome->fill($validated);

        if (! $outcome->isDirty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No changes detected',
                'data' => $outcome,
            ], 200);
        }

        $outcome->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Outcome updated successfully',
            'data' => $outcome->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Outcome $outcome): JsonResponse
    {
        $outcome->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Outcome deleted successfully',
        ], 200);
    }
}
