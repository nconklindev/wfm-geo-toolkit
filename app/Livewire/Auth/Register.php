<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Rules\UkgEmailDomainRule;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public string $username = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'username' => ['required', 'string', 'max:25', 'unique:'.User::class, 'alpha_dash'],
            'email' => [
                'required', 'string', 'email:rfc,spoof,dns,filter,strict', 'max:255',
                'unique:'.User::class,
                new UkgEmailDomainRule(),
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(10)->mixedCase()->numbers()->uncompromised(3)->symbols(),
            ],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('welcome', absolute: false), navigate: true);
    }
}
