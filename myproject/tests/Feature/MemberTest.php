<?php

use App\Models\Member;
use App\Models\MembershipLevel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('members page can search by name, category, and level', function () {
    $member = Member::create([
        'first_name' => 'Ana',
        'last_name' => 'Santos',
        'email' => 'ana@example.com',
        'category' => 'Professional',
        'level' => 'Gold',
        'status' => 'active',
    ]);

    $response = $this->get(route('members.index', [
        'search' => 'Ana',
        'category' => 'Professional',
        'level' => 'Gold',
    ]));

    $response->assertOk()->assertSee($member->full_name)->assertSee($member->email);
});

test('a member can be registered', function () {
    MembershipLevel::create(['name' => 'Silver', 'fee' => 1000, 'validity_months' => 12]);

    $response = $this->post(route('members.store'), [
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => 'juan@example.com',
        'phone' => '09123456789',
        'address' => 'Manila',
        'category' => 'Student',
        'level' => 'Silver',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('members.index'));
    $this->assertDatabaseHas('members', ['email' => 'juan@example.com', 'category' => 'Student']);
});

test('a member can be deactivated', function () {
    $member = Member::create([
        'first_name' => 'Mia',
        'last_name' => 'Reyes',
        'email' => 'mia@example.com',
        'category' => 'Community',
        'level' => 'Basic',
        'status' => 'active',
    ]);

    $response = $this->patch(route('members.deactivate', $member));

    $response->assertRedirect();
    $this->assertDatabaseHas('members', ['id' => $member->id, 'status' => 'inactive']);
});
