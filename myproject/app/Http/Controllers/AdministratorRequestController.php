<?php

namespace App\Http\Controllers;

use App\Models\AdministratorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdministratorRequestController extends Controller
{
    public function create(): View
    {
        return view('member.admin-request');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        AdministratorRequest::updateOrCreate(
            ['user_id' => Auth::id(), 'status' => 'pending'],
            ['reason' => $validated['reason']]
        );

        return redirect()->route('member.admin-requests')->with('success', 'Administrator access request submitted.');
    }

    public function memberIndex(): View
    {
        $requests = AdministratorRequest::query()->where('user_id', Auth::id())->latest()->get();

        return view('member.admin-requests', compact('requests'));
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->toString();
        $requests = AdministratorRequest::query()
            ->with(['user.member'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('administrator-requests.index', compact('requests', 'status'));
    }

    public function approve(AdministratorRequest $administratorRequest): RedirectResponse
    {
        $administratorRequest->update([
            'status' => 'approved',
            'decided_by' => Auth::id(),
            'decided_at' => now(),
            'decision_message' => 'Administrator access approved.',
        ]);

        $administratorRequest->user->update(['role' => 'admin']);

        return back()->with('success', 'Administrator access approved.');
    }

    public function reject(Request $request, AdministratorRequest $administratorRequest): RedirectResponse
    {
        $validated = $request->validate(['decision_message' => ['required', 'string', 'max:2000']]);

        $administratorRequest->update([
            'status' => 'rejected',
            'decided_by' => Auth::id(),
            'decided_at' => now(),
            'decision_message' => $validated['decision_message'],
        ]);

        return back()->with('success', 'Administrator access request rejected.');
    }
}
