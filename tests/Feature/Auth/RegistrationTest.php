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

test('new user can register with a UKG email address', function () {
    $password = Factory::create()->password(10, 25);
    $email = 'test@ukg.com';

    $responseUkgEmail = Livewire::test(Register::class)
        ->set('username', 'test_ukg_user')
        ->set('email', $email)
        ->set('password', $password)
        ->set('password_confirmation', $password)
        ->call('register');

    $responseUkgEmail
        ->assertHasNoErrors()
        ->assertRedirect(route('welcome', absolute: false));

    $this->assertAuthenticated();
});

test('non-UKG domain user cannot register', function () {
    $password = Factory::create()->password(10, 25);
    $responseNonUkgEmail = Livewire::test(Register::class)
        ->set('username', 'test_non_ukg_user')
        ->set('email', 'test@gmail.com')
        ->set('password', $password)
        ->set('password_confirmation', $password)
        ->call('register');

    $responseNonUkgEmail
        ->assertHasErrors(['email']);

    $this->assertGuest();
});
