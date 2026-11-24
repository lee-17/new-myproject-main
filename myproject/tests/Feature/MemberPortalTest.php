<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->category = MembershipCategory::create(['name' => 'Student', 'fee' => 250]);
    $this->level = MembershipLevel::create(['name' => 'Basic', 'fee' => 500, 'validity_months' => 12]);
});

test('member registration keeps the location map and weather preview', function () {
    $this->get(route('member.register'))
        ->assertOk()
        ->assertSee('member-map')
        ->assertSee('member-weather')
        ->assertSee(route('weather.show'));
});

test('a visitor can create a member account with an initial transaction', function () {
    $response = $this->post(route('member.register.store'), [
        'first_name' => 'Mia',
        'last_name' => 'Reyes',
        'email' => 'mia@example.test',
        'password' => 'password',
        'password_confirmation' => 'password',
        'membership_category_id' => $this->category->id,
        'membership_level_id' => $this->level->id,
    ]);

    $member = Member::where('email', 'mia@example.test')->firstOrFail();

    $response->assertRedirect(route('member.dashboard'));
    $this->assertAuthenticatedAs($member->user);
    $this->assertDatabaseHas('users', ['email' => 'mia@example.test', 'role' => 'member']);
    $this->assertDatabaseHas('membership_transactions', [
        'member_id' => $member->id,
        'type' => 'application',
        'status' => 'pending',
    ]);
});

test('a member can view the portal and complete an assigned task', function () {
    $user = User::factory()->create(['role' => 'member']);
    $member = Member::create([
        'user_id' => $user->id,
        'first_name' => 'Ana',
        'last_name' => 'Cruz',
        'email' => 'ana@example.test',
        'category' => 'Student',
        'level' => 'Basic',
        'membership_category_id' => $this->category->id,
        'membership_level_id' => $this->level->id,
        'status' => 'active',
    ]);
    $task = Task::create(['member_id' => $member->id, 'task_title' => 'Read the guide', 'description' => 'Review the portal guide.']);

    $this->actingAs($user)->get(route('member.dashboard'))->assertOk()->assertSee('Ana');
    $this->actingAs($user)->patch(route('member.tasks.complete', $task))->assertRedirect();

    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'completed', 'completed' => true]);
});

test('a member can submit an administrator access request', function () {
    $user = User::factory()->create(['role' => 'member']);
    $member = Member::create([
        'user_id' => $user->id,
        'first_name' => 'Sam',
        'last_name' => 'Lee',
        'email' => 'sam@example.test',
        'category' => 'Student',
        'level' => 'Basic',
        'membership_category_id' => $this->category->id,
        'membership_level_id' => $this->level->id,
        'status' => 'active',
    ]);

    $this->actingAs($user)->post(route('member.administrator-request.store'), [
        'reason' => 'I help manage membership records.',
    ])->assertRedirect(route('member.admin-requests'));

    $this->assertDatabaseHas('administrator_requests', ['user_id' => $user->id, 'status' => 'pending']);
});
