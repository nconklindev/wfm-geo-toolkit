<?php

use App\Livewire\Auth\Register;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $password = Factory::create()->password(10);
    $response = Livewire::test(Register::class)
        ->set('username', 'testuser')
        ->set('email', 'test@example.com')
        ->set('password', $password)
        ->set('password_confirmation', $password)
        ->call('register');

    // Debug the errors if registration failed
    if (count($errors = $response->errors()) > 0) {
        dump('Registration validation errors:', $errors);
    }

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});
