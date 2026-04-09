<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                @php
                    $unreadNotificationsCount = auth()->user()->unreadNotifications()->count();
                @endphp
                <a
                    href="{{ route('notifications.index') }}"
                    wire:navigate
                    class="relative ms-auto inline-flex lg:ms-0"
                    aria-label="{{ __('Notifications') }}"
                >
                    <flux:button variant="ghost" size="sm" icon="bell" class="!px-2" />
                    @if ($unreadNotificationsCount > 0)
                        <span
                            class="absolute -end-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-medium text-white"
                        >
                            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="tag" :href="route('finance.categories.index')" :current="request()->routeIs('finance.categories.index')" wire:navigate>
                        {{ __('Categories') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-trending-down" :href="route('finance.expenses.index')" :current="request()->routeIs('finance.expenses.*')" wire:navigate>
                        {{ __('Expenses') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-trending-up" :href="route('finance.incomes.index')" :current="request()->routeIs('finance.incomes.*')" wire:navigate>
                        {{ __('Incomes') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('investments.goals.index')" :current="request()->routeIs('investments.goals.*')" wire:navigate>
                        {{ __('Investments') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('support.contact')" :current="request()->routeIs('support.contact')" wire:navigate>
                        {{ __('Contact us') }}
                    </flux:sidebar.item>
                    @can('viewAny', \App\Models\Ticket::class)
                        <flux:sidebar.item icon="lifebuoy" :href="route('support.kanban')" :current="request()->routeIs('support.kanban')" wire:navigate>
                            {{ __('Support') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            @php
                $mobileUnreadNotificationsCount = auth()->user()->unreadNotifications()->count();
            @endphp
            <a
                href="{{ route('notifications.index') }}"
                wire:navigate
                class="relative ms-1 inline-flex"
                aria-label="{{ __('Notifications') }}"
            >
                <flux:button variant="ghost" size="sm" icon="bell" class="!px-2" />
                @if ($mobileUnreadNotificationsCount > 0)
                    <span
                        class="absolute -end-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-medium text-white"
                    >
                        {{ $mobileUnreadNotificationsCount > 9 ? '9+' : $mobileUnreadNotificationsCount }}
                    </span>
                @endif
            </a>

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
