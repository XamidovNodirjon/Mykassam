<?php

use App\Models\User;

it('suggests a login built from the last nine phone digits and an eight character password', function () {
    $owner = User::factory()->owner()->create();

    $response = $this->actingAs($owner)->getJson(route('owner.seller-credentials', ['phone' => '+998 93 111 22 33']));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.login', 's931112233');
    expect($response->json('data.password'))->toMatch('/^[a-z2-9]{8}$/');
});

it('adds a numeric suffix when the login is already taken', function () {
    $owner = User::factory()->owner()->create();
    User::factory()->seller()->create(['login' => 's931112233']);

    $response = $this->actingAs($owner)->getJson(route('owner.seller-credentials', ['phone' => '998931112233']));

    $response->assertJsonPath('data.login', 's931112233_2');
});

it('rejects an incomplete phone number', function () {
    $owner = User::factory()->owner()->create();

    $response = $this->actingAs($owner)->getJson(route('owner.seller-credentials', ['phone' => '90123']));

    $response->assertUnprocessable()->assertJsonValidationErrors('phone');
});
