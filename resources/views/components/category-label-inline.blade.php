@props(['category'])

@if ($category)
    <div {{ $attributes->class('flex min-w-0 items-center gap-2') }}>
        <div
            class="flex size-7 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700/60"
        >
            <x-category-icon :name="$category->icon" class="size-4 text-zinc-600 dark:text-zinc-300" />
        </div>
        <span class="min-w-0 truncate">{{ $category->name }}</span>
    </div>
@else
    <span {{ $attributes }}>—</span>
@endif
