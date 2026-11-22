<?php

use App\Models\Task;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('the tasks page displays saved tasks', function () {
    $task = Task::create([
        'task_title' => 'Review the tutorial',
        'description' => 'Connect the model to the database.',
    ]);

    $response = $this->get(route('tasks.index'));

    $response->assertOk()->assertSee($task->task_title)->assertSee($task->description);
});

test('a valid task can be added', function () {
    $response = $this->post(route('tasks.store'), [
        'task_title' => 'Build the task page',
        'description' => 'Add a task form and list.',
    ]);

    $response->assertRedirect(route('tasks.index'));
    $this->assertDatabaseHas('tasks', [
        'task_title' => 'Build the task page',
        'description' => 'Add a task form and list.',
    ]);
});

test('a task requires a title', function () {
    $response = $this->from(route('tasks.index'))->post(route('tasks.store'), [
        'task_title' => '',
    ]);

    $response->assertRedirect(route('tasks.index'))->assertSessionHasErrors('task_title');
});
