<?php

namespace App\Http\Controllers;

use App\Models\MembershipCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = MembershipCategory::query()->withCount('members')->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.form', ['category' => new MembershipCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        MembershipCategory::create($this->validatedData($request));

        return redirect()->route('categories.index')->with('success', 'Membership category created.');
    }

    public function edit(MembershipCategory $category): View
    {
        return view('categories.form', compact('category'));
    }

    public function update(Request $request, MembershipCategory $category): RedirectResponse
    {
        $category->update($this->validatedData($request, $category));

        return redirect()->route('categories.index')->with('success', 'Membership category updated.');
    }

    public function destroy(MembershipCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Membership category deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?MembershipCategory $category = null): array
    {
        $uniqueName = 'unique:membership_categories,name';

        if ($category !== null) {
            $uniqueName .= ','.$category->id;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:100', $uniqueName],
            'level' => ['nullable', 'string', 'max:100'],
            'fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
