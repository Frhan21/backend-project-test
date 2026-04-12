<?php

namespace App\Http\Controllers;

use App\Models\Housing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HousingController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $housings = Housing::query()->latest('id')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => $housings->isEmpty()
                ? 'No housings found'
                : 'Housings retrieved successfully',
            'data' => $housings,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resident_id' => ['required', 'integer', Rule::exists('residents', 'id')],
            'house_id' => ['required', 'integer', Rule::exists('houses', 'id')],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['required', 'boolean'],
        ]);

        $housing = Housing::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Housing created successfully',
            'data' => $housing,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Housing $housing): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Housing retrieved successfully',
            'data' => $housing,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Housing $housing): JsonResponse
    {
        $validated = $request->validate([
            'resident_id' => ['sometimes', 'required', 'integer', Rule::exists('residents', 'id')],
            'house_id' => ['sometimes', 'required', 'integer', Rule::exists('houses', 'id')],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);

        if ($validated === []) {
            return response()->json([
                'status' => 'error',
                'message' => 'No update data was provided',
            ], 422);
        }

        $startDate = $validated['start_date'] ?? $housing->start_date;
        $endDate = $validated['end_date'] ?? $housing->end_date;

        if ($endDate !== null && $endDate < $startDate) {
            return response()->json([
                'status' => 'error',
                'message' => 'The end date must be after or equal to the start date',
            ], 422);
        }

        $housing->fill($validated);

        if (! $housing->isDirty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No changes detected',
                'data' => $housing,
            ], 200);
        }

        $housing->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Housing updated successfully',
            'data' => $housing->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Housing $housing): JsonResponse
    {
        $housing->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Housing deleted successfully',
        ], 200);
    }
}
