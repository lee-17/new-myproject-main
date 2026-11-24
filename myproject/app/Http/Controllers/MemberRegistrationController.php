<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MemberRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.member-register', [
            'categories' => MembershipCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'levels' => MembershipLevel::query()->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:members,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'membership_category_id' => ['required', 'exists:membership_categories,id'],
            'membership_level_id' => ['required', 'exists:membership_levels,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $category = MembershipCategory::findOrFail($validated['membership_category_id']);
        $level = MembershipLevel::findOrFail($validated['membership_level_id']);

        $user = DB::transaction(function () use ($validated, $category, $level): User {
            $user = User::create([
                'name' => $validated['first_name'].' '.$validated['last_name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'member',
                'email_verified_at' => now(),
            ]);

            $member = Member::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'category' => $category->name,
                'level' => $level->name,
                'membership_category_id' => $category->id,
                'membership_level_id' => $level->id,
                'status' => 'active',
            ]);

            MembershipTransaction::create([
                'member_id' => $member->id,
                'membership_level_id' => $level->id,
                'transaction_number' => null,
                'type' => 'application',
                'amount' => $level->fee,
                'status' => 'pending',
                'notes' => 'Initial membership request created during member registration.',
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('member.dashboard')->with('success', 'Member account created. Your initial membership request is pending payment and approval.');
    }
}
