<x-layouts::auth.modern-split
    :title="__('Forgot password')"
    :marketing-title="__('Reset in minutes.')"
    :marketing-subtitle="__('We’ll email you a secure link to set a new password.')"
>
    <div class="flex flex-col gap-6">
        <div class="text-center">
            <flux:heading size="xl">{{ __('Forgot password') }}</flux:heading>
            <flux:subheading>{{ __('Enter your email to receive a password reset link') }}</flux:subheading>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
                icon:leading="envelope"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Email password reset link') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Or, return to') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth.modern-split>
