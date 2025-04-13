<?php

use App\Models\KnownPlace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create a known place', function () {
    $user = \App\Models\User::factory()->create();

    $this->actingAs($user)->post(route('known-places.store'), [
        'name' => 'Create Test',
        'description' => 'This is a test known place',
        'latitude' => 12.234,
        'longitude' => 82.3466,
        'radius' => 100,
        'accuracy' => 100,
        'validation_order' => ['gps'],
    ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('known_places', [
        'user_id' => $user->id,
        'name' => 'Create Test'
    ]);
});

test('users can create known place with the same name', function () {
    $user1 = \App\Models\User::factory()->create();

    // Create a Known Place as the first user in the db
    KnownPlace::factory()->create([
        'user_id' => $user1->id,
        'name' => 'Duplicate Place Name',
        'latitude' => 12.234,
        'longitude' => 82.3466,
        'radius' => 100,
        'accuracy' => 100,
        'validation_order' => ['gps']
    ]);

    // Create the second user for testing
    $user2 = \App\Models\User::factory()->create();

    // As the second user, create a Known Place with the same name
    // user_id will be different
    // name will be the same
    // In this instance we are verifying that two users can have the same named Known Places
    $this->actingAs($user2)->post(route('known-places.store'), [
        'name' => 'Duplicate Place Name',
        'latitude' => 12.234,
        'longitude' => 82.3466,
        'radius' => 100,
        'accuracy' => 100,
        'validation_order' => ['gps']
    ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    // Assert that the db has the two records
    $this->assertDatabaseCount('known_places', 2);

    // Assert that the db has the first record for user 1
    $this->assertDatabaseHas('known_places', [
        'user_id' => $user1->id,
        'name' => 'Duplicate Place Name'
    ]);

    // Asesrt that the db has the second record for user 2
    $this->assertDatabaseHas('known_places', [
        'user_id' => $user2->id,
        'name' => 'Duplicate Place Name'
    ]);
});
