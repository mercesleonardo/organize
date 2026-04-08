<x-layouts::auth.modern-split
    :title="__('Log in')"
    :marketing-title="__('Welcome back!')"
    :marketing-subtitle="__('Pick up right where you left off — your finances, organized.')"
>
    <div class="flex flex-col gap-6">
        <div class="text-center">
            <flux:heading size="xl">{{ __('Log in to your account') }}</flux:heading>
            <flux:subheading>{{ __('Enter your email and password below to log in') }}</flux:subheading>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
                icon:leading="envelope"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                    icon:leading="lock-closed"
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between gap-3">
                <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />
            </div>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        <div class="relative">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-zinc-200 dark:border-zinc-800"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-white px-2 text-zinc-500 dark:bg-zinc-900/40 dark:text-zinc-400">
                    {{ __('Or continue with') }}
                </span>
            </div>
        </div>

        <x-google-oauth-button :href="route('auth.google.redirect')">
            {{ __('Continue with Google') }}
        </x-google-oauth-button>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth.modern-split>
