<?php

use App\Models\KnownPlace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot update known places', function () {
    $user = \App\Models\User::factory()->create();

    $knownPlace = KnownPlace::factory()->create([
        'user_id' => $user->id,
        'name' => 'Guest Update Test'
    ]);

    // Attempt to update the known place as a guest with PATCH (non-logged in user)
    $response = $this->patch(route('known-places.update', $knownPlace), [
        'name' => 'Updated Name'
    ]);

    // Assert that user is redirected to login page
    $response->assertRedirect(route('login'));

    // Assert that the Known Place record still exists in the database
    $this->assertDatabaseHas('known_places', ['id' => $knownPlace->id]);
});

test('users cannot update known places they do not own', function () {
    $owner = \App\Models\User::factory()->create();

    // Create another user
    $anotherUser = App\Models\User::factory()->create();

    $knownPlace = KnownPlace::factory()->create([
        'user_id' => $owner->id,
        'name' => 'Test'
    ]);

    // Log in as the other user (not the owner)
    $this->actingAs($anotherUser);

    // Attempt to update the known place
    $response = $this->patch(route('known-places.update', $knownPlace), [
        'name' => 'Updated Name'
    ]);

    // Assert that a 403 is returned
    $response->assertForbidden();

    // Assert that the Known Place still exists in the database
    $this->assertDatabaseHas('known_places', ['id' => $knownPlace->id]);
});
