<?php

namespace App\Http\Controllers;

use App\Models\MembershipLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LevelController extends Controller
{
    public function index(): View
    {
        $levels = MembershipLevel::query()->withCount('members')->orderBy('id')->get();

        return view('levels.index', compact('levels'));
    }

    public function create(): View
    {
        return view('levels.form', ['level' => new MembershipLevel]);
    }

    public function store(Request $request): RedirectResponse
    {
        MembershipLevel::create($this->validatedData($request));

        return redirect()->route('levels.index')->with('success', 'Membership level created.');
    }

    public function edit(MembershipLevel $level): View
    {
        return view('levels.form', compact('level'));
    }

    public function update(Request $request, MembershipLevel $level): RedirectResponse
    {
        $level->update($this->validatedData($request, $level));

        return redirect()->route('levels.index')->with('success', 'Membership level updated.');
    }

    public function destroy(MembershipLevel $level): RedirectResponse
    {
        $level->delete();

        return redirect()->route('levels.index')->with('success', 'Membership level deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?MembershipLevel $level = null): array
    {
        $uniqueName = 'unique:membership_levels,name';

        if ($level !== null) {
            $uniqueName .= ','.$level->id;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:100', $uniqueName],
            'fee' => ['required', 'numeric', 'min:0'],
            'validity_months' => ['required', 'integer', 'min:1', 'max:120'],
            'benefits' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
