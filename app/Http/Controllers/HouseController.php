<?php

namespace App\Http\Controllers;

use App\Models\House;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HouseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $houses = House::query()->latest('id')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => $houses->isEmpty()
                ? 'No houses found'
                : 'Houses retrieved successfully',
            'data' => $houses,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $house = House::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'House created successfully',
            'data' => $house,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(House $house): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'House retrieved successfully',
            'data' => $house,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, House $house): JsonResponse
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

        $house->fill($validated);

        if (! $house->isDirty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No changes detected',
                'data' => $house,
            ]);
        }

        $house->save();

        return response()->json([
            'status' => 'success',
            'message' => 'House updated successfully',
            'data' => $house->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(House $house): JsonResponse
    {
        $house->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'House deleted successfully',
        ]);
    }
}
