@php($title = __('Home'))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-950">
        <div class="relative isolate overflow-hidden">
            <div
                class="pointer-events-none absolute inset-x-0 -top-40 -z-10 h-[480px] bg-[radial-gradient(ellipse_80%_60%_at_50%_-10%,rgba(16,185,129,0.18),transparent)] dark:bg-[radial-gradient(ellipse_80%_60%_at_50%_-10%,rgba(16,185,129,0.12),transparent)]"
                aria-hidden="true"
            ></div>

            {{-- Header --}}
            <header class="border-b border-zinc-200/80 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-950/80">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="group flex items-center gap-2.5 font-semibold text-zinc-900 dark:text-white">
                        <span class="flex size-9 items-center justify-center rounded-lg bg-zinc-100 transition group-hover:bg-emerald-100/80 dark:bg-zinc-800 dark:group-hover:bg-emerald-950/50">
                            <x-app-logo-icon class="size-6 fill-current text-zinc-900 dark:text-white" />
                        </span>
                        <span>{{ config('app.name', 'Laravel') }}</span>
                    </a>

                    <nav class="flex flex-wrap items-center justify-end gap-2 sm:gap-3" aria-label="{{ __('Primary navigation') }}">
                        @auth
                            <flux:button variant="ghost" :href="route('dashboard')" size="sm">
                                {{ __('Dashboard') }}
                            </flux:button>
                        @else
                            @if (Route::has('login'))
                                <flux:button variant="ghost" :href="route('login')" size="sm">
                                    {{ __('Sign in') }}
                                </flux:button>
                            @endif
                            @if (Route::has('register'))
                                <flux:button variant="primary" :href="route('register')" size="sm">
                                    {{ __('Create account') }}
                                </flux:button>
                            @endif
                        @endauth
                    </nav>
                </div>
            </header>

            <main>
                {{-- Hero --}}
                <section class="mx-auto max-w-6xl px-4 pb-20 pt-16 sm:px-6 sm:pb-28 sm:pt-24 lg:px-8 lg:pb-32 lg:pt-28">
                    <div class="mx-auto max-w-3xl text-center">
                        <flux:heading level="1" size="xl" class="text-balance text-4xl font-semibold tracking-tight text-zinc-900 sm:text-5xl lg:text-6xl dark:text-white">
                            {{ __('Master your money. Unlock your freedom.') }}
                        </flux:heading>
                        <flux:text class="mx-auto mt-6 max-w-2xl text-pretty text-lg leading-relaxed text-zinc-600 dark:text-zinc-400">
                            {{ __('A simple, smart platform to organize your income, track expenses from daily to yearly, and plan your financial future.') }}
                        </flux:text>
                        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row sm:gap-5">
                            @auth
                                <flux:button variant="primary" :href="route('dashboard')" class="!h-12 !px-8 !text-base" icon:trailing="arrow-right">
                                    {{ __('Go to dashboard') }}
                                </flux:button>
                            @else
                                @if (Route::has('register'))
                                    <flux:button variant="primary" :href="route('register')" class="!h-12 !px-8 !text-base" icon:leading="sparkles">
                                        {{ __('Start free') }}
                                    </flux:button>
                                @elseif (Route::has('login'))
                                    <flux:button variant="primary" :href="route('login')" class="!h-12 !px-8 !text-base" icon:trailing="arrow-right">
                                        {{ __('Start free') }}
                                    </flux:button>
                                @endif
                            @endauth
                        </div>
                    </div>
                </section>

                {{-- Features --}}
                <section class="border-t border-zinc-200/80 bg-white py-20 dark:border-zinc-800 dark:bg-zinc-900/40 sm:py-28" aria-labelledby="features-heading">
                    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                        <div class="mx-auto max-w-2xl text-center">
                            <flux:heading id="features-heading" level="2" size="lg" class="text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                                {{ __('Everything you need in one place') }}
                            </flux:heading>
                            <flux:text class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
                                {{ __('Built for clarity—without the complexity.') }}
                            </flux:text>
                        </div>

                        <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
                            <article class="flex flex-col rounded-2xl border border-zinc-200/90 bg-zinc-50/80 p-8 shadow-sm transition hover:border-emerald-200/80 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900/60 dark:hover:border-emerald-800/50">
                                <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-950/60">
                                    <flux:icon icon="chart-bar-square" class="size-6 text-emerald-700 dark:text-emerald-400" />
                                </div>
                                <flux:heading level="3" size="lg" class="mb-2 text-zinc-900 dark:text-white">
                                    {{ __('Full control') }}
                                </flux:heading>
                                <flux:text class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                                    {{ __('A clear view of spending and earnings daily, weekly, monthly, and yearly.') }}
                                </flux:text>
                            </article>

                            <article class="flex flex-col rounded-2xl border border-zinc-200/90 bg-zinc-50/80 p-8 shadow-sm transition hover:border-emerald-200/80 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900/60 dark:hover:border-emerald-800/50">
                                <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-950/60">
                                    <flux:icon icon="calendar-days" class="size-6 text-emerald-700 dark:text-emerald-400" />
                                </div>
                                <flux:heading level="3" size="lg" class="mb-2 text-zinc-900 dark:text-white">
                                    {{ __('Installment intelligence') }}
                                </flux:heading>
                                <flux:text class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                                    {{ __('Add installment purchases and let the system automatically project the next months.') }}
                                </flux:text>
                            </article>

                            <article class="flex flex-col rounded-2xl border border-zinc-200/90 bg-zinc-50/80 p-8 shadow-sm transition hover:border-emerald-200/80 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900/60 dark:hover:border-emerald-800/50">
                                <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-950/60">
                                    <flux:icon icon="check-badge" class="size-6 text-emerald-700 dark:text-emerald-400" />
                                </div>
                                <flux:heading level="3" size="lg" class="mb-2 text-zinc-900 dark:text-white">
                                    {{ __('Status management') }}
                                </flux:heading>
                                <flux:text class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                                    {{ __('Know what is paid and what is pending—with a single click.') }}
                                </flux:text>
                            </article>

                            <article class="flex flex-col rounded-2xl border border-zinc-200/90 bg-zinc-50/80 p-8 shadow-sm transition hover:border-emerald-200/80 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900/60 dark:hover:border-emerald-800/50">
                                <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-950/60">
                                    <flux:icon icon="tag" class="size-6 text-emerald-700 dark:text-emerald-400" />
                                </div>
                                <flux:heading level="3" size="lg" class="mb-2 text-zinc-900 dark:text-white">
                                    {{ __('Categories') }}
                                </flux:heading>
                                <flux:text class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                                    {{ __('Organize your finances with fully customizable categories.') }}
                                </flux:text>
                            </article>
                        </div>
                    </div>
                </section>

                {{-- CTA final --}}
                <section class="mx-auto max-w-6xl px-4 pb-20 sm:px-6 lg:px-8 lg:pb-28">
                    <div
                        class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-600 to-emerald-800 px-8 py-14 text-center shadow-lg sm:px-12 sm:py-16 dark:from-emerald-700 dark:to-emerald-950"
                    >
                        <div class="pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
                        <div class="pointer-events-none absolute -bottom-20 -left-10 size-56 rounded-full bg-black/10 blur-2xl" aria-hidden="true"></div>
                        <flux:heading level="2" size="lg" class="relative text-balance text-2xl font-semibold text-white sm:text-3xl">
                            {{ __('Ready to organize your finances? It takes less than a minute.') }}
                        </flux:heading>
                        <div class="relative mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row sm:gap-5">
                            @auth
                                <flux:button variant="filled" :href="route('dashboard')" class="!border-0 !bg-white !text-emerald-800 hover:!bg-zinc-100 dark:!bg-zinc-100 dark:!text-emerald-900">
                                    {{ __('Open dashboard') }}
                                </flux:button>
                            @else
                                @if (Route::has('register'))
                                    <flux:button variant="filled" :href="route('register')" class="!border-0 !bg-white !text-emerald-800 hover:!bg-zinc-100 dark:!bg-zinc-100 dark:!text-emerald-900">
                                        {{ __('Create my account') }}
                                    </flux:button>
                                @endif
                                @if (Route::has('login'))
                                    <flux:button variant="ghost" :href="route('login')" class="!text-white hover:!bg-white/10">
                                        {{ __('I already have an account') }}
                                    </flux:button>
                                @endif
                            @endauth
                        </div>
                    </div>
                </section>
            </main>

            {{-- Footer --}}
            <footer class="border-t border-zinc-200 bg-white py-10 dark:border-zinc-800 dark:bg-zinc-950">
                <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-6 px-4 sm:flex-row sm:px-6 lg:px-8">
                    <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                        © {{ date('Y') }}
                        {{ config('app.name', 'Laravel') }}. {{ __('All rights reserved.') }}
                    </p>
                    <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm" aria-label="{{ __('Footer') }}">
                        <a href="{{ route('home') }}" class="text-zinc-600 underline-offset-4 transition hover:text-emerald-700 hover:underline dark:text-zinc-400 dark:hover:text-emerald-400">
                            {{ __('Home') }}
                        </a>
                        @guest
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="text-zinc-600 underline-offset-4 transition hover:text-emerald-700 hover:underline dark:text-zinc-400 dark:hover:text-emerald-400">
                                    {{ __('Sign in') }}
                                </a>
                            @endif
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-zinc-600 underline-offset-4 transition hover:text-emerald-700 hover:underline dark:text-zinc-400 dark:hover:text-emerald-400">
                                    {{ __('Create account') }}
                                </a>
                            @endif
                        @endguest
                    </nav>
                </div>
            </footer>
        </div>

        @fluxScripts
    </body>
</html>
