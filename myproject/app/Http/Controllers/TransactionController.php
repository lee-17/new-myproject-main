<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->toString();
        $search = $request->string('search')->trim()->toString();

        $transactions = MembershipTransaction::query()
            ->with(['member', 'level'])
            ->withSum('payments', 'amount')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($transactionQuery) use ($search): void {
                    $transactionQuery
                        ->where('transaction_number', 'like', "%{$search}%")
                        ->orWhereHas('member', function ($memberQuery) use ($search): void {
                            $memberQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('membership_number', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        return view('transactions.index', compact('transactions', 'status', 'search'));
    }

    public function create(Request $request): View
    {
        $selectedMember = $request->integer('member_id');

        return view('transactions.create', [
            'members' => Member::query()->where('status', 'active')->orderBy('last_name')->orderBy('first_name')->get(),
            'levels' => MembershipLevel::query()->orderBy('id')->get(),
            'selectedMember' => $selectedMember,
            'transactionType' => $request->string('type')->toString() ?: 'application',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'membership_level_id' => ['required', 'exists:membership_levels,id'],
            'type' => ['required', 'in:application,renewal'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'effective_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $transaction = DB::transaction(function () use ($validated): MembershipTransaction {
            return MembershipTransaction::create([
                ...$validated,
                'created_by' => Auth::id(),
                'status' => 'pending',
            ]);
        });

        return redirect()->route('transactions.show', $transaction)->with('success', 'Transaction created and awaiting payment.');
    }

    public function show(MembershipTransaction $transaction): View
    {
        $transaction->load(['member', 'level', 'payments.receiver']);

        return view('transactions.show', compact('transaction'));
    }

    public function approve(MembershipTransaction $transaction): RedirectResponse
    {
        $transaction->load('payments');

        if ($transaction->status !== 'paid' || $transaction->paid_amount < (float) $transaction->amount) {
            return back()->with('error', 'Only fully paid transactions may be approved.');
        }

        $effectiveDate = $transaction->effective_date ?? now()->toDateString();
        $expiresAt = $transaction->expires_at;

        if ($transaction->level !== null) {
            $expiresAt = now()->parse($effectiveDate)->addMonths($transaction->level->validity_months)->toDateString();
        }

        DB::transaction(function () use ($transaction, $effectiveDate, $expiresAt): void {
            $transaction->update([
                'status' => 'approved',
                'effective_date' => $effectiveDate,
                'expires_at' => $expiresAt,
                'approved_at' => now(),
            ]);

            $transaction->member->update([
                'membership_level_id' => $transaction->membership_level_id,
                'level' => $transaction->level?->name ?? $transaction->member->level,
                'membership_expires_at' => $expiresAt,
                'status' => 'active',
            ]);
        });

        return back()->with('success', 'Transaction approved and member validity updated.');
    }

    public function cancel(MembershipTransaction $transaction): RedirectResponse
    {
        if (in_array($transaction->status, ['approved', 'cancelled'], true)) {
            return back()->with('error', 'This transaction can no longer be cancelled.');
        }

        $transaction->update(['status' => 'cancelled']);

        return back()->with('success', 'Transaction cancelled.');
    }

    public function history(Member $member): View
    {
        $member->load(['categoryRelation', 'levelRelation', 'transactions.level', 'transactions.payments']);

        return view('transactions.history', compact('member'));
    }
}
