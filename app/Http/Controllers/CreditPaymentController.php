<?php

namespace App\Http\Controllers;

use App\Models\CreditPayment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreditPaymentController extends Controller
{
    public function index()
    {
        $search = trim((string) request()->query('q', ''));

        $payments = CreditPayment::with(['sale.customer', 'user'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->whereHas('sale', function ($saleQuery) use ($search): void {
                        $saleQuery->where('invoice_no', 'like', "%{$search}%");
                    })->orWhereHas('customer', function ($customerQuery) use ($search): void {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })->orWhereHas('user', function ($userQuery) use ($search): void {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('credit-payments.index', compact('payments', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('sales.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $sale = Sale::lockForUpdate()->findOrFail($validated['sale_id']);

            if ($sale->payment_type !== 'credit') {
                throw ValidationException::withMessages([
                    'sale_id' => 'Only credit sales accept credit payments.',
                ]);
            }

            if ((float) $sale->due_amount <= 0) {
                throw ValidationException::withMessages([
                    'sale_id' => 'This sale is already fully paid.',
                ]);
            }

            $amount = min((float) $validated['amount'], (float) $sale->due_amount);

            CreditPayment::create([
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'user_id' => $request->user()->id,
                'payment_date' => $validated['payment_date'],
                'amount' => $amount,
                'note' => $validated['note'] ?? null,
            ]);

            $sale->paid_amount = round((float) $sale->paid_amount + $amount, 2);
            $sale->due_amount = round(max(0, (float) $sale->total_amount - (float) $sale->paid_amount), 2);
            $sale->updatePaymentStatus();
            $sale->save();
        });

        return back()->with('success', 'Credit payment recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('credit-payments.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return redirect()->route('credit-payments.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return redirect()->route('credit-payments.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return redirect()->route('credit-payments.index');
    }
}
