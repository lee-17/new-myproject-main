@extends('layouts.app', ['title' => 'My Tasks'])

@section('content')
<section class="page-heading"><div><p class="eyebrow">Member portal</p><h1>My tasks</h1><p class="page-subtitle">Read assigned instructions, complete each activity, and keep your progress up to date.</p></div></section>
<section class="panel filter-panel"><form class="filter-form" action="{{ route('member.tasks') }}" method="GET"><div><label for="status">Show tasks</label><select id="status" name="status"><option value="">All tasks</option><option value="pending" @selected($status === 'pending')>Pending</option><option value="completed" @selected($status === 'completed')>Completed</option></select></div><button class="secondary-button" type="submit">Apply filter</button></form></section>
<section class="task-list">
    @forelse ($tasks as $task)
        <article class="task-item panel"><div class="task-item-heading"><div><span class="status-pill status-{{ $task->status }}">{{ ucfirst($task->status) }}</span><h2>{{ $task->task_title }}</h2></div><small>{{ $task->created_at->format('M j, Y') }}</small></div><p>{{ $task->description ?: 'No instructions provided.' }}</p>@if ($task->status !== 'completed')<form action="{{ route('member.tasks.complete', $task) }}" method="POST">@csrf @method('PATCH')<button class="primary-button" type="submit">Mark completed</button></form>@else<p class="task-completed-note">Completed {{ $task->completed_at?->format('M j, Y g:i A') }}</p>@endif</article>
    @empty
        <section class="panel empty-state"><span class="empty-icon">✦</span><h3>No tasks found</h3><p>Assigned activities will appear here.</p></section>
    @endforelse
</section>
@endsection
