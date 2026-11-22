<?php

use App\Models\MembershipCategory;
use App\Notifications\MembershipApplicationSubmitted;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

uses(LazilyRefreshDatabase::class);

test('the membership form displays active categories', function () {
    $category = MembershipCategory::create(['name' => 'Regular', 'level' => 'Standard', 'fee' => 500]);

    $response = $this->get(route('membership.create'));

    $response->assertOk()->assertSee($category->name)->assertSee('Membership application');
});

test('a valid application is stored and queues its notification', function () {
    Notification::fake();
    $category = MembershipCategory::create(['name' => 'Regular', 'fee' => 500]);

    $response = $this->post(route('membership.store'), [
        'membership_category_id' => $category->id,
        'full_name' => 'Ana Cruz',
        'email' => 'ana@example.test',
        'latitude' => 14.5995,
        'longitude' => 120.9842,
    ]);

    $response->assertRedirect(route('membership.create'))->assertSessionHas('success');
    $this->assertDatabaseHas('membership_applications', [
        'email' => 'ana@example.test',
        'status' => 'pending',
        'membership_category_id' => $category->id,
    ]);
    Notification::assertSentOnDemand(MembershipApplicationSubmitted::class);
});

test('the weather endpoint validates coordinates and returns normalized weather data', function () {
    Http::fake([
        'https://api.openweathermap.org/data/2.5/weather*' => Http::response([
            'name' => 'Manila',
            'main' => ['temp' => 30.5, 'humidity' => 70],
            'weather' => [['description' => 'scattered clouds']],
        ]),
    ]);

    $response = $this->getJson(route('weather.show', ['lat' => 14.5995, 'lon' => 120.9842]));

    $response->assertOk()->assertJson([
        'place' => 'Manila',
        'temperature' => 30.5,
        'description' => 'scattered clouds',
        'humidity' => 70,
    ]);
});

test('the weather endpoint rejects invalid coordinates', function () {
    $response = $this->getJson(route('weather.show', ['lat' => 100, 'lon' => 120]));

    $response->assertUnprocessable()->assertJsonValidationErrors(['lat']);
});
