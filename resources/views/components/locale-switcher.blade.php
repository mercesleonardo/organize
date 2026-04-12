@props([
    'variant' => 'menu',
])

@php
    $locales = config('locales.supported', []);
    $current = app()->getLocale();
    $currentMeta = $locales[$current] ?? (count($locales) ? reset($locales) : ['native' => '', 'flag' => '🌐']);
@endphp

{{--
  Dentro do menu do perfil (flux:menu) não usar flux:dropdown aninhado: o componente de dropdown
  do Flux não funciona de forma fiável dentro de outro menu/dropdown. Aqui listamos os idiomas
  como itens de menu normais com POST.
--}}
@if ($variant === 'menu')
    <div class="px-2 pb-1 pt-0.5">
        <div class="mb-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">
            {{ __('Language') }}
        </div>
        @foreach ($locales as $code => $meta)
            <form method="POST" action="{{ route('locale.update') }}" class="block w-full">
                @csrf
                <input type="hidden" name="locale" value="{{ $code }}" />
                <flux:menu.item
                    as="button"
                    type="submit"
                    class="w-full cursor-pointer !justify-start gap-2 {{ $current === $code ? 'font-semibold text-emerald-700 dark:text-emerald-400' : '' }}"
                >
                    <span class="text-lg leading-none" aria-hidden="true">{{ $meta['flag'] ?? '🌐' }}</span>
                    <span class="truncate">{{ $meta['native'] }}</span>
                    @if ($current === $code)
                        <flux:icon icon="check" variant="micro" class="ms-auto size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                    @endif
                </flux:menu.item>
            </form>
        @endforeach
    </div>
@else
    <div {{ $attributes->class('inline-flex') }}>
        <flux:dropdown position="bottom" align="end">
            <flux:button
                variant="ghost"
                size="sm"
                class="gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/60"
                type="button"
                :aria-label="__('Language')"
            >
                <span class="text-lg leading-none" aria-hidden="true">{{ $currentMeta['flag'] ?? '🌐' }}</span>
                <span class="hidden text-sm sm:inline">{{ $currentMeta['native'] }}</span>
                <flux:icon icon="chevron-down" variant="micro" class="size-4 text-zinc-500" />
            </flux:button>

            <flux:menu>
                @foreach ($locales as $code => $meta)
                    <form method="POST" action="{{ route('locale.update') }}" class="block w-full">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $code }}" />
                        <flux:menu.item
                            as="button"
                            type="submit"
                            class="w-full cursor-pointer !justify-start gap-2 {{ $current === $code ? 'font-semibold text-emerald-700 dark:text-emerald-400' : '' }}"
                        >
                            <span class="text-lg leading-none" aria-hidden="true">{{ $meta['flag'] ?? '🌐' }}</span>
                            <span>{{ $meta['native'] }}</span>
                            @if ($current === $code)
                                <flux:icon icon="check" variant="micro" class="ms-auto size-4 text-emerald-600 dark:text-emerald-400" />
                            @endif
                        </flux:menu.item>
                    </form>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    </div>
@endif
