<x-layouts::auth.modern-split
    :title="__('Email verification')"
    :marketing-title="__('One last step.')"
    :marketing-subtitle="__('Verify your email to keep your account secure.')"
>
    <div class="flex flex-col gap-6">
        <div class="text-center">
            <flux:heading size="xl">{{ __('Email verification') }}</flux:heading>
            <flux:subheading>{{ __('Please verify your email address by clicking on the link we just emailed to you.') }}</flux:subheading>
        </div>

        @if (session('status') == 'verification-link-sent')
            <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </flux:text>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Resend verification email') }}
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">
                    {{ __('Log out') }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts::auth.modern-split>
