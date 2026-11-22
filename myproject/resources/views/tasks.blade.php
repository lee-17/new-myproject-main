<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>My Tasks</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="task-page">
        <main class="task-container">
            <nav class="page-nav"><a class="active" href="{{ route('tasks.index') }}">Tasks</a><a href="{{ route('members.index') }}">Members</a></nav>
            <header class="task-header"><p class="task-kicker">Laravel task manager</p><h1>My Tasks</h1><p class="task-intro">Add a task below and your saved tasks will appear here.</p></header>
            @if (session('success'))<p class="success-message">{{ session('success') }}</p>@endif
            <section class="task-card"><h2>Add a task</h2><form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <label for="task_title">Task title</label><input id="task_title" name="task_title" type="text" value="{{ old('task_title') }}" required>
                @error('task_title')<p class="form-error">{{ $message }}</p>@enderror
                <label for="description">Description</label><textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
                <button type="submit">Add Task</button>
            </form></section>
            <section class="saved-tasks"><h2>Saved Tasks</h2>
                @forelse ($tasks as $task)
                    <article class="task-item"><h3>{{ $task->task_title }}</h3><p>{{ $task->description ?: 'No description provided.' }}</p><span>Status: {{ $task->completed ? 'Completed' : 'Not completed' }}</span></article>
                @empty
                    <p class="empty-message">No tasks have been added yet.</p>
                @endforelse
            </section>
        </main>
    </body>
</html>
