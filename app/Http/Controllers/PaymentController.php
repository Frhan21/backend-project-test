<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Housing;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $payments = Payment::query()->latest('id')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => $payments->isEmpty()
                ? 'No payments found'
                : 'Payments retrieved successfully',
            'data' => $payments,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'housing_id' => ['required', 'integer', Rule::exists('housings', 'id')],
            'fee_id' => ['required', 'integer', Rule::exists('fees', 'id')],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
            'nominal' => ['nullable', 'numeric', 'min:0'],
            'duration' => ['nullable', 'integer', 'between:1,12'],
        ]);

        $housing = Housing::findOrFail($validated['housing_id']);
        $fee = Fee::findOrFail($validated['fee_id']);
        $basePeriod = Carbon::createFromDate($validated['year'], $validated['month'], 1)->startOfMonth();
        $duration = $validated['duration'] ?? 1;

        if (! $this->isHousingActiveForPeriod($housing, $basePeriod, $duration)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Housing is not active for the selected payment period',
            ], 422);
        }

        $createdPayments = [];
        $skippedPayments = [];

        DB::transaction(function () use ($validated, $fee, $basePeriod, $duration, &$createdPayments, &$skippedPayments): void {
            for ($offset = 0; $offset < $duration; $offset++) {
                $period = $basePeriod->copy()->addMonthsNoOverflow($offset);

                $attributes = [
                    'housing_id' => $validated['housing_id'],
                    'fee_id' => $validated['fee_id'],
                    'month' => (int) $period->format('n'),
                    'year' => (int) $period->format('Y'),
                ];

                $payment = Payment::query()->firstWhere($attributes);

                if ($payment !== null) {
                    $skippedPayments[] = $payment;
                    continue;
                }

                $createdPayments[] = Payment::create($attributes + [
                    'nominal' => $validated['nominal'] ?? $fee->default_nominal,
                    'payment_date' => null,
                    'is_paid' => false,
                ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => $duration > 1
                ? 'Payments created successfully'
                : 'Payment created successfully',
            'data' => [
                'housing_id' => $housing->id,
                'fee' => [
                    'id' => $fee->id,
                    'name' => $fee->name,
                ],
                'requested_duration' => $duration,
                'created' => $createdPayments,
                'skipped' => $skippedPayments,
            ],
        ], count($createdPayments) > 0 ? 201 : 200);
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fee_id' => ['required', 'integer', Rule::exists('fees', 'id')],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
        ]);

        $fee = Fee::findOrFail($validated['fee_id']);
        $basePeriod = Carbon::createFromDate($validated['year'], $validated['month'], 1)->startOfMonth();

        $eligibleHousings = Housing::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (Housing $housing) => $this->isHousingActiveForPeriod($housing, $basePeriod, 1));

        $createdPayments = [];
        $skippedPayments = [];

        DB::transaction(function () use ($eligibleHousings, $validated, $fee, &$createdPayments, &$skippedPayments): void {
            foreach ($eligibleHousings as $housing) {
                $attributes = [
                    'housing_id' => $housing->id,
                    'fee_id' => $validated['fee_id'],
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                ];

                $payment = Payment::query()->firstWhere($attributes);

                if ($payment !== null) {
                    $skippedPayments[] = $payment;
                    continue;
                }

                $createdPayments[] = Payment::create($attributes + [
                    'nominal' => $fee->default_nominal,
                    'payment_date' => null,
                    'is_paid' => false,
                ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Payments generated successfully',
            'data' => [
                'fee' => [
                    'id' => $fee->id,
                    'name' => $fee->name,
                ],
                'month' => $validated['month'],
                'year' => $validated['year'],
                'eligible_housings' => $eligibleHousings->count(),
                'created' => $createdPayments,
                'skipped' => $skippedPayments,
            ],
        ], count($createdPayments) > 0 ? 201 : 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Payment retrieved successfully',
            'data' => $payment,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'housing_id' => ['sometimes', 'required', 'integer', Rule::exists('housings', 'id')],
            'fee_id' => ['sometimes', 'required', 'integer', Rule::exists('fees', 'id')],
            'nominal' => ['sometimes', 'required', 'numeric', 'min:0'],
            'payment_date' => ['sometimes', 'required', 'date'],
            'month' => ['sometimes', 'required', 'integer', 'between:1,12'],
            'year' => ['sometimes', 'required', 'integer', 'digits:4'],
            'is_paid' => ['sometimes', 'required', 'boolean'],
        ]);

        if ($validated === []) {
            return response()->json([
                'status' => 'error',
                'message' => 'No update data was provided',
            ], 422);
        }

        $targetHousing = isset($validated['housing_id'])
            ? Housing::findOrFail($validated['housing_id'])
            : Housing::findOrFail($payment->housing_id);
        $targetFee = isset($validated['fee_id'])
            ? Fee::findOrFail($validated['fee_id'])
            : Fee::findOrFail($payment->fee_id);
        $targetMonth = $validated['month'] ?? $payment->month;
        $targetYear = $validated['year'] ?? $payment->year;
        $targetPeriod = Carbon::createFromDate($targetYear, $targetMonth, 1)->startOfMonth();

        if (! $this->isHousingActiveForPeriod($targetHousing, $targetPeriod, 1)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Housing is not active for the selected payment period',
            ], 422);
        }

        $duplicatePayment = Payment::query()
            ->where('housing_id', $validated['housing_id'] ?? $payment->housing_id)
            ->where('fee_id', $validated['fee_id'] ?? $payment->fee_id)
            ->where('month', $targetMonth)
            ->where('year', $targetYear)
            ->whereKeyNot($payment->id)
            ->exists();

        if ($duplicatePayment) {
            return response()->json([
                'status' => 'error',
                'message' => 'A payment for this housing, fee, month, and year already exists',
            ], 422);
        }

        if (! array_key_exists('nominal', $validated) && isset($validated['fee_id'])) {
            $validated['nominal'] = $targetFee->default_nominal;
        }

        if (array_key_exists('is_paid', $validated)) {
            if ($validated['is_paid'] && ! array_key_exists('payment_date', $validated)) {
                $validated['payment_date'] = now()->toDateTimeString();
            }

            if (! $validated['is_paid']) {
                $validated['payment_date'] = null;
            }
        }

        $payment->fill($validated);

        if (! $payment->isDirty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No changes detected',
                'data' => $payment,
            ]);
        }

        $payment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment updated successfully',
            'data' => $payment->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully',
        ], 200);
    }

    private function isHousingActiveForPeriod(Housing $housing, Carbon $basePeriod, int $duration): bool
    {
        if (! $housing->is_active) {
            return false;
        }

        $startBoundary = $basePeriod->copy()->startOfMonth();
        $endBoundary = $basePeriod->copy()->addMonthsNoOverflow($duration - 1)->endOfMonth();
        $housingStart = Carbon::parse($housing->start_date)->startOfDay();

        if ($housingStart->gt($endBoundary)) {
            return false;
        }

        if ($housing->end_date === null) {
            return true;
        }

        $housingEnd = Carbon::parse($housing->end_date)->endOfDay();

        return $housingEnd->gte($startBoundary);
    }
}
