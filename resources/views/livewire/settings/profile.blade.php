@php use Illuminate\Contracts\Auth\MustVerifyEmail; @endphp
<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your username and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input
                wire:model="username"
                :label="__('Username')"
                type="text"
                required
                autofocus
                autocomplete="username"
            />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link
                                class="cursor-pointer text-sm"
                                wire:click.prevent="resendVerificationNotification"
                            >
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="!dark:text-green-400 mt-2 font-medium !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <flux:separator class="mb-4" />
        <flux:input
            icon="key"
            label="Your Support ID"
            :value="auth()->user()->support_id"
            copyable
            disabled
            readonly
        ></flux:input>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
