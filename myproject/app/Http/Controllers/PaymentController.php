<?php

namespace App\Http\Controllers;

use App\Models\MembershipTransaction;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function create(MembershipTransaction $transaction): View
    {
        $transaction->load(['member', 'payments']);

        return view('payments.create', compact('transaction'));
    }

    public function store(Request $request, MembershipTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'in:cash,bank_transfer,card,e_wallet'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $transaction->load('payments');
        $balance = $transaction->balance;

        if ((float) $validated['amount'] > $balance) {
            return back()->withErrors(['amount' => 'Payment cannot be greater than the remaining balance of ₱'.number_format($balance, 2).'.'])->withInput();
        }

        DB::transaction(function () use ($validated, $transaction): void {
            Payment::create([
                ...$validated,
                'received_by' => Auth::id(),
                'membership_transaction_id' => $transaction->id,
            ]);

            $transaction->load('payments');

            if ($transaction->paid_amount >= (float) $transaction->amount) {
                $transaction->update(['status' => 'paid']);
            }
        });

        return redirect()->route('transactions.show', $transaction)->with('success', 'Payment recorded successfully.');
    }

    public function receipt(MembershipTransaction $transaction): View
    {
        $transaction->load(['member', 'level', 'payments']);

        return view('payments.receipt', compact('transaction'));
    }
}
