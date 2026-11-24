<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('the application returns a successful response', function () {
    $response = $this->actingAs(User::factory()->create())->get('/');

    $response->assertStatus(200);
});
