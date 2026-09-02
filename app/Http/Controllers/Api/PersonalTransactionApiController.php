<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalTransaction;
use Illuminate\Http\Request;

class PersonalTransactionApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $transactions = PersonalTransaction::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();

        $data = $transactions->map(function ($t) {
            return [
                'id' => (string) ($t->client_id ?: $t->id),
                'server_id' => $t->id,
                'title' => $t->title,
                'amount' => (int) round((float) $t->amount),
                'type' => $t->type,
                'category' => $t->category,
                'date' => $t->date?->toIso8601String(),
                'note' => $t->note,
                'is_hr_salary' => (bool) $t->is_hr_salary,
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|string',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'category' => 'nullable|string|max:100',
            'date' => 'required',
            'note' => 'nullable|string',
            'is_hr_salary' => 'nullable|boolean',
        ]);

        $user = $request->user();

        // Check if already exists by client_id to avoid duplicate on retry
        if (!empty($validated['client_id'])) {
            $existing = PersonalTransaction::where('user_id', $user->id)
                ->where('client_id', $validated['client_id'])
                ->first();

            if ($existing) {
                $existing->update([
                    'title' => $validated['title'],
                    'amount' => $validated['amount'],
                    'type' => $validated['type'],
                    'category' => $validated['category'] ?? 'Umum',
                    'date' => \Carbon\Carbon::parse($validated['date']),
                    'note' => $validated['note'] ?? null,
                    'is_hr_salary' => $validated['is_hr_salary'] ?? false,
                ]);

                return response()->json([
                    'message' => 'Transaksi berhasil diperbarui.',
                    'data' => [
                        'id' => (string) $existing->client_id,
                        'server_id' => $existing->id,
                        'title' => $existing->title,
                        'amount' => (int) round((float) $existing->amount),
                        'type' => $existing->type,
                        'category' => $existing->category,
                        'date' => $existing->date?->toIso8601String(),
                        'note' => $existing->note,
                        'is_hr_salary' => (bool) $existing->is_hr_salary,
                    ],
                ]);
            }
        }

        $transaction = PersonalTransaction::create([
            'user_id' => $user->id,
            'client_id' => $validated['client_id'] ?? null,
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'category' => $validated['category'] ?? 'Umum',
            'date' => \Carbon\Carbon::parse($validated['date']),
            'note' => $validated['note'] ?? null,
            'is_hr_salary' => $validated['is_hr_salary'] ?? false,
        ]);

        return response()->json([
            'message' => 'Transaksi berhasil disimpan.',
            'data' => [
                'id' => (string) ($transaction->client_id ?: $transaction->id),
                'server_id' => $transaction->id,
                'title' => $transaction->title,
                'amount' => (int) round((float) $transaction->amount),
                'type' => $transaction->type,
                'category' => $transaction->category,
                'date' => $transaction->date?->toIso8601String(),
                'note' => $transaction->note,
                'is_hr_salary' => (bool) $transaction->is_hr_salary,
            ],
        ], 201);
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $transaction = PersonalTransaction::where('user_id', $user->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('client_id', $id);
            })
            ->first();

        if ($transaction) {
            $transaction->delete();
        }

        return response()->json([
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }

    public function sync(Request $request)
    {
        $validated = $request->validate([
            'transactions' => 'present|array',
        ]);

        $user = $request->user();
        $incoming = $validated['transactions'];

        foreach ($incoming as $item) {
            if (empty($item['title'])) continue;

            $clientId = $item['id'] ?? null;
            $date = !empty($item['date']) ? \Carbon\Carbon::parse($item['date']) : now();

            PersonalTransaction::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'client_id' => $clientId,
                ],
                [
                    'title' => $item['title'],
                    'amount' => (float) ($item['amount'] ?? 0),
                    'type' => $item['type'] ?? 'expense',
                    'category' => $item['category'] ?? 'Umum',
                    'date' => $date,
                    'note' => $item['note'] ?? null,
                    'is_hr_salary' => $item['is_hr_salary'] ?? false,
                ]
            );
        }

        // Return all latest transactions from server
        return $this->index($request);
    }
}
