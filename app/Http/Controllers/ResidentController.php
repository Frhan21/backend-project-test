<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResidentController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $residents = Resident::query()->latest('id')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => $residents->isEmpty()
                ? 'No residents found'
                : 'Residents retrieved successfully',
            'data' => $residents,
            'meta' => [
                'current_page' => $residents->currentPage(),
                'last_page' => $residents->lastPage(),
                'per_page' => $residents->perPage(),
                'total' => $residents->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ktp_image' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:kontrak,tetap'],
            'no_telp' => ['required', 'string', 'max:30'],
            'is_married' => ['required', 'boolean'],
        ]);

        $resident = Resident::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Resident created successfully',
            'data' => $resident,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Resident $resident): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Resident retrieved successfully',
            'data' => $resident,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Resident $resident): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'ktp_image' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'in:kontrak,tetap'],
            'no_telp' => ['sometimes', 'required', 'string', 'max:30'],
            'is_married' => ['sometimes', 'required', 'boolean'],
        ]);

        if ($validated === []) {
            return response()->json([
                'status' => 'error',
                'message' => 'No update data was provided',
            ], 422);
        }

        $resident->fill($validated);

        if (! $resident->isDirty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No changes detected',
                'data' => $resident,
            ]);
        }

        $resident->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Resident updated successfully',
            'data' => $resident->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resident $resident): JsonResponse
    {
        $resident->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Resident deleted successfully',
        ]);
    }
}
