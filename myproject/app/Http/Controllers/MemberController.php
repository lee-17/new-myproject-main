<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    /** @var list<string> */
    private const CATEGORIES = ['Student', 'Regular', 'Senior Citizen', 'Corporate'];

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $category = $request->string('category')->trim()->toString();
        $level = $request->string('level')->trim()->toString();

        $members = Member::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($memberQuery) use ($search) {
                    $memberQuery
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('membership_number', 'like', "%{$search}%");
                });
            })
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($level !== '', fn ($query) => $query->where('level', $level))
            ->latest()
            ->get();

        $categories = $this->categoryOptions();
        $levels = MembershipLevel::query()->orderBy('id')->get();
        $stats = [
            'total' => Member::count(),
            'active' => Member::where('status', 'active')->count(),
            'inactive' => Member::where('status', 'inactive')->count(),
            'categories' => count($categories),
        ];

        return view('members.index', compact('members', 'categories', 'levels', 'stats', 'search', 'category', 'level'));
    }

    public function create(): View
    {
        return view('members.create', ['membershipLevels' => MembershipLevel::orderBy('id')->get(), 'categories' => $this->categoryOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Member::create($this->validatedMemberData($request));

        return redirect()->route('members.index')->with('success', 'Member registered successfully.');
    }

    public function show(Member $member): View
    {
        $member->load(['categoryRelation', 'levelRelation', 'transactions.level', 'transactions.payments']);

        return view('members.show', compact('member'));
    }

    public function edit(Member $member): View
    {
        return view('members.edit', ['member' => $member, 'membershipLevels' => MembershipLevel::orderBy('id')->get(), 'categories' => $this->categoryOptions()]);
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $member->update($this->validatedMemberData($request, $member));

        return redirect()->route('members.show', $member)->with('success', 'Member details updated.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $member->delete();

        return redirect()->route('members.index')->with('success', 'Member deleted.');
    }

    public function deactivate(Member $member): RedirectResponse
    {
        $member->update(['status' => $member->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', $member->status === 'active' ? 'Member reactivated.' : 'Member deactivated.');
    }

    /**
     * @return array<string, string|null>
     */
    private function validatedMemberData(Request $request, ?Member $member = null): array
    {
        $emailRule = 'unique:members,email';

        if ($member !== null) {
            $emailRule .= ",{$member->id}";
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'in:'.implode(',', self::CATEGORIES)],
            'level' => ['required', 'exists:membership_levels,name'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $category = MembershipCategory::query()->firstOrCreate(
            ['name' => $validated['category']],
            ['fee' => 0, 'is_active' => true]
        );
        $level = MembershipLevel::query()->where('name', $validated['level'])->firstOrFail();

        return [
            ...$validated,
            'membership_category_id' => $category->id,
            'membership_level_id' => $level->id,
        ];
    }

    /**
     * @return list<string>
     */
    private function categoryOptions(): array
    {
        return collect(self::CATEGORIES)
            ->merge(MembershipCategory::query()->orderBy('name')->pluck('name'))
            ->unique()
            ->values()
            ->all();
    }
}
