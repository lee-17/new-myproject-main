<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MemberPortalController extends Controller
{
    public function dashboard(): View
    {
        $member = $this->member(['categoryRelation', 'levelRelation']);
        $tasks = $member->tasks()->latest()->limit(5)->get();
        $transactions = $member->transactions()->with('level')->latest()->limit(5)->get();

        return view('member.dashboard', compact('member', 'tasks', 'transactions'));
    }

    public function profile(): View
    {
        return view('member.profile', ['member' => $this->member(['categoryRelation', 'levelRelation'])]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $member = $this->member();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $member->update($validated);
        $member->user?->update(['name' => $member->full_name]);

        return back()->with('success', 'Your profile has been updated.');
    }

    public function tasks(Request $request): View
    {
        $member = $this->member();
        $status = $request->string('status')->trim()->toString();
        $tasks = $member->tasks()
            ->when($status === 'completed', fn ($query) => $query->where('status', 'completed'))
            ->when($status === 'pending', fn ($query) => $query->where('status', 'pending'))
            ->latest()
            ->get();

        return view('member.tasks', compact('member', 'tasks', 'status'));
    }

    public function completeTask(Task $task): RedirectResponse
    {
        abort_unless($task->member_id === $this->member()->id, 403);

        $task->update(['completed' => true, 'status' => 'completed', 'completed_at' => now()]);

        return back()->with('success', 'Task marked as completed.');
    }

    public function transactions(): View
    {
        $member = $this->member(['categoryRelation', 'levelRelation']);
        $member->load(['transactions.level', 'transactions.payments']);

        return view('member.transactions', compact('member'));
    }

    public function createTransactionRequest(Request $request): View
    {
        $member = $this->member(['levelRelation']);
        $type = $request->string('type')->trim()->toString() ?: 'renewal';
        abort_unless(in_array($type, ['upgrade', 'renewal'], true), 404);

        return view('member.transaction-request', [
            'member' => $member,
            'levels' => MembershipLevel::query()->orderBy('fee')->get(),
            'type' => $type,
        ]);
    }

    public function storeTransactionRequest(Request $request): RedirectResponse
    {
        $member = $this->member(['levelRelation']);
        $validated = $request->validate([
            'type' => ['required', 'in:upgrade,renewal'],
            'membership_level_id' => ['required', 'exists:membership_levels,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($member->transactions()->whereIn('status', ['pending', 'paid'])->exists()) {
            return back()->with('error', 'Finish your existing pending or paid transaction before starting another request.');
        }

        $level = MembershipLevel::findOrFail($validated['membership_level_id']);

        if ($validated['type'] === 'upgrade' && (float) $level->fee <= (float) ($member->levelRelation?->fee ?? 0)) {
            return back()->with('error', 'An upgrade must use a membership level with a higher fee than your current level.');
        }

        MembershipTransaction::create([
            'member_id' => $member->id,
            'membership_level_id' => $level->id,
            'type' => $validated['type'],
            'amount' => $level->fee,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('member.transactions')->with('success', ucfirst($validated['type']).' request submitted. Arrange payment with an administrator.');
    }

    private function member(array $with = []): Member
    {
        return Auth::user()->member()->with($with)->firstOrFail();
    }
}
