<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->toString();
        $tasks = Task::query()
            ->with('member')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();
        $members = Member::query()->where('status', 'active')->whereNotNull('user_id')->orderBy('last_name')->get();

        return view('tasks', compact('tasks', 'members', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'task_title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'member_id' => ['nullable', 'exists:members,id'],
        ]);

        Task::create([
            ...$validated,
            'assigned_by' => Auth::id(),
            'status' => 'pending',
            'completed' => false,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'task_title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'member_id' => ['nullable', 'exists:members,id'],
        ]);

        $task->update([...$validated, 'status' => 'pending', 'completed' => false, 'completed_at' => null]);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function toggle(Task $task): RedirectResponse
    {
        $completed = ! $task->completed;
        $task->update([
            'completed' => $completed,
            'status' => $completed ? 'completed' : 'pending',
            'completed_at' => $completed ? now() : null,
        ]);

        return back()->with('success', $completed ? 'Task marked as completed.' : 'Task marked as pending.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back()->with('success', 'Task deleted.');
    }
}
