<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'members' => Member::count(),
            'active_members' => Member::where('status', 'active')->count(),
            'pending_transactions' => MembershipTransaction::where('status', 'pending')->count(),
            'collected_payments' => Payment::sum('amount'),
        ];

        $recentTransactions = MembershipTransaction::query()
            ->with(['member', 'level'])
            ->latest()
            ->limit(6)
            ->get();

        $recentMembers = Member::query()->latest()->limit(5)->get();

        return view('dashboard', compact('stats', 'recentTransactions', 'recentMembers'));
    }
}
