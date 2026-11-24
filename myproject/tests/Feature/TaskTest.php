<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

test('the tasks page displays saved tasks', function () {
    $task = Task::create([
        'task_title' => 'Review the tutorial',
        'description' => 'Connect the model to the database.',
    ]);

    $response = $this->actingAs($this->user)->get(route('tasks.index'));

    $response->assertOk()->assertSee($task->task_title)->assertSee($task->description);
});

test('a valid task can be added', function () {
    $response = $this->actingAs($this->user)->post(route('tasks.store'), [
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
    $response = $this->actingAs($this->user)->from(route('tasks.index'))->post(route('tasks.store'), [
        'task_title' => '',
    ]);

    $response->assertRedirect(route('tasks.index'))->assertSessionHasErrors('task_title');
});
