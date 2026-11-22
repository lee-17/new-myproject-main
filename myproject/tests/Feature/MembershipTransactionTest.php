<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->category = MembershipCategory::create(['name' => 'Regular', 'fee' => 500]);
    $this->level = MembershipLevel::create([
        'name' => 'Gold',
        'fee' => 1500,
        'validity_months' => 12,
        'benefits' => 'Priority support',
    ]);
    $this->member = Member::create([
        'first_name' => 'Ana',
        'last_name' => 'Cruz',
        'email' => 'ana@example.test',
        'category' => 'Regular',
        'level' => 'Gold',
        'membership_category_id' => $this->category->id,
        'membership_level_id' => $this->level->id,
        'status' => 'active',
    ]);
});

test('an authenticated staff member can create an application transaction', function () {
    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'member_id' => $this->member->id,
        'membership_level_id' => $this->level->id,
        'type' => 'application',
        'amount' => 1500,
        'effective_date' => '2026-12-01',
    ]);

    $transaction = MembershipTransaction::first();

    $response->assertRedirect(route('transactions.show', $transaction));
    expect($transaction->transaction_number)->toStartWith('TXN-');
    $this->assertDatabaseHas('membership_transactions', [
        'member_id' => $this->member->id,
        'status' => 'pending',
        'type' => 'application',
    ]);
});

test('a full payment changes the transaction to paid and allows approval', function () {
    $transaction = MembershipTransaction::create([
        'member_id' => $this->member->id,
        'membership_level_id' => $this->level->id,
        'created_by' => $this->user->id,
        'type' => 'renewal',
        'amount' => 1500,
        'status' => 'pending',
    ]);

    $this->actingAs($this->user)->post(route('payments.store', $transaction), [
        'amount' => 1500,
        'payment_method' => 'cash',
        'paid_at' => '2026-11-22 10:00:00',
    ])->assertRedirect(route('transactions.show', $transaction));

    $this->assertDatabaseHas('membership_transactions', ['id' => $transaction->id, 'status' => 'paid']);
    $this->actingAs($this->user)->patch(route('transactions.approve', $transaction));

    $this->assertDatabaseHas('membership_transactions', ['id' => $transaction->id, 'status' => 'approved']);
    $this->assertDatabaseHas('members', ['id' => $this->member->id, 'status' => 'active']);
});

test('an unpaid transaction cannot be approved', function () {
    $transaction = MembershipTransaction::create([
        'member_id' => $this->member->id,
        'membership_level_id' => $this->level->id,
        'created_by' => $this->user->id,
        'type' => 'application',
        'amount' => 1500,
        'status' => 'pending',
    ]);

    $this->actingAs($this->user)->patch(route('transactions.approve', $transaction))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('membership_transactions', ['id' => $transaction->id, 'status' => 'pending']);
});
