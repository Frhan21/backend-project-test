<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeController
{
    /**
     * Display a listing of the resource.
     */
    public function index() : JsonResponse
    {
        $fees = Fee::query()->latest('id')->get();

        return response()->json([
            'status' => 'success',
            'message' => $fees->isEmpty()
                ? 'No fees found'
                : 'Fees retrieved successfully',
            'data' => $fees,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) : JsonResponse
    {
        $validate = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'default_nominal' => ['required', 'numeric', 'min:0'],
            'period_type' => ['required', 'in:bulan,tahun']
        ]);

        $fee = Fee::create($validate);

        return response()->json([
            'status' => 'success',
            'message' => 'Fee created successfully',
            'data' => $fee,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Fee $fee): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Fee retrieved successfully',
            'data' => $fee,
        ], 200); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fee $fee)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'default_nominal' => ['sometimes', 'required', 'numeric', 'min:0'],
            'period_type' => ['sometimes', 'required', 'in:bulan,tahun']
        ]);

        if ($validated === []) {
            return response()->json([
                'status' => 'error',
                'message' => 'No update data was provided',
            ], 422);
        }

        $fee->fill($validated);

        if (! $fee->isDirty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No changes detected',
                'data' => $fee,
            ]);
        }

        $fee->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment updated successfully',
            'data' => $fee->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fee $fee): JsonResponse
    {
        $fee->delete(); 
        return response()->json([
            'status' => 'success',
            'message' => 'Fee deleted successfully',
        ], 200);
    }
}
