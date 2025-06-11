<?php

use App\Models\KnownPlace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot delete known places', function () {
    $user = User::factory()->create();

    $knownPlace = KnownPlace::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test'
    ]);

    // Attempt to delete the known place as a guest (non-logged in user)
    $response = $this->delete(route('known-places.destroy', $knownPlace));

    // Assert that user is redirected to login page
    $response->assertRedirect(route('login'));

    // Assert that the Known Place record still exists in the database
    $this->assertDatabaseHas('known_places', ['id' => $knownPlace->id]);
});

test('users cannot delete known places they do not own', function () {
    // Create owner of the known place
    $owner = User::factory()->create();

    // Create another user
    $anotherUser = User::factory()->create();

    $knownPlace = KnownPlace::factory()->create([
        'user_id' => $owner->id,
        'name' => 'Test'
    ]);

    // Log in as the other user (not the owner)
    $this->actingAs($anotherUser);

    // Attempt to delete the known place
    $response = $this->delete(route('known-places.destroy', $knownPlace));

    // Assert that a 403 is returned
    $response->assertForbidden();

    // Assert that the Known Place record still exists in the database
    $this->assertDatabaseHas('known_places', ['id' => $knownPlace->id]);
});
