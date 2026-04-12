@props([
    'title' => null,
    'marketingTitle' => null,
    'marketingSubtitle' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-950">
        <div class="relative grid min-h-dvh lg:grid-cols-2">
            <div class="relative hidden overflow-hidden lg:block">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-700 via-emerald-600 to-zinc-950"></div>
                <div class="absolute inset-0 opacity-40 bg-[radial-gradient(ellipse_70%_60%_at_30%_20%,rgba(255,255,255,0.25),transparent)]"></div>

                <div class="relative z-10 flex h-full min-h-0 flex-col p-10 text-white">
                    <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3 text-lg font-semibold tracking-tight" wire:navigate>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                            <x-app-logo-icon class="h-7 fill-current text-white" />
                        </span>
                        <span>{{ config('app.name', 'Laravel') }}</span>
                    </a>

                    <div class="flex min-h-0 flex-1 flex-col justify-center py-10">
                        <div class="mx-auto flex w-full max-w-md flex-col gap-3">
                            <article class="flex gap-4 rounded-2xl border border-white/15 bg-white/10 p-4 shadow-sm backdrop-blur-sm">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white/15">
                                    <flux:icon icon="chart-bar-square" class="size-6 text-white" />
                                </span>
                                <div class="min-w-0 space-y-1">
                                    <flux:heading level="3" size="lg" class="text-white">
                                        {{ __('Periods & balance') }}
                                    </flux:heading>
                                    <flux:text class="text-sm leading-relaxed text-white/75">
                                        {{ __('View income, expenses, and available balance across daily to yearly views.') }}
                                    </flux:text>
                                </div>
                            </article>

                            <article class="flex gap-4 rounded-2xl border border-white/15 bg-white/10 p-4 shadow-sm backdrop-blur-sm">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white/15">
                                    <flux:icon icon="calendar-days" class="size-6 text-white" />
                                </span>
                                <div class="min-w-0 space-y-1">
                                    <flux:heading level="3" size="lg" class="text-white">
                                        {{ __('Installments & categories') }}
                                    </flux:heading>
                                    <flux:text class="text-sm leading-relaxed text-white/75">
                                        {{ __('Plan installment purchases and keep spending tidy with your own categories.') }}
                                    </flux:text>
                                </div>
                            </article>

                            <article class="flex gap-4 rounded-2xl border border-white/15 bg-white/10 p-4 shadow-sm backdrop-blur-sm">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white/15">
                                    <flux:icon icon="banknotes" class="size-6 text-white" />
                                </span>
                                <div class="min-w-0 space-y-1">
                                    <flux:heading level="3" size="lg" class="text-white">
                                        {{ __('Investments') }}
                                    </flux:heading>
                                    <flux:text class="text-sm leading-relaxed text-white/75">
                                        {{ __('Track goals and contributions without affecting your balance.') }}
                                    </flux:text>
                                </div>
                            </article>
                        </div>
                    </div>

                    <div class="mt-auto max-w-md shrink-0 space-y-3 border-t border-white/10 pt-8">
                        <flux:heading level="2" size="xl" class="text-white">
                            {{ $marketingTitle ?? __('Welcome back!') }}
                        </flux:heading>
                        <flux:text class="text-white/80">
                            {{ $marketingSubtitle ?? __('A clean, green way to organize your personal finances.') }}
                        </flux:text>

                        <div class="pt-1 text-sm text-white/70">
                            <span class="inline-flex items-center gap-2">
                                <span class="size-2 rounded-full bg-white/60"></span>
                                {{ __('Secure, fast, and simple.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center px-4 py-10 sm:px-6 lg:px-10">
                <div class="w-full max-w-md">
                    <a href="{{ route('home') }}" class="mb-8 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                            <x-app-logo-icon class="size-7 fill-current text-white" />
                        </span>
                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/40 sm:p-8">
                        {{ $slot }}

                        <div class="mt-6 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                            <x-locale-switcher variant="inline" class="justify-center" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @fluxScripts
    </body>
</html>

