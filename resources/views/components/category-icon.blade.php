@props([
    'name' => null,
    'fallback' => 'tag',
])

@php
    $raw = is_string($name) ? trim($name) : '';

    if ($raw === '') {
        $raw = $fallback;
    }

    $useLucide = str_contains($raw, ':');
    $lucideSlug = '';

    if ($useLucide) {
        [$lib, $lucideSlug] = array_pad(explode(':', $raw, 2), 2, '');
        $lib = strtolower(trim((string) $lib));
        $lucideSlug = strtolower(trim((string) $lucideSlug));
        $useLucide = $lib === 'lucide' && $lucideSlug !== '' && preg_match('/^[a-z0-9\-]+$/', $lucideSlug) === 1;
    }

    $fluxCandidate = $useLucide ? $fallback : $raw;

    if (! $useLucide) {
        $fluxCandidate = preg_match('/^[a-z0-9\-]+$/', $fluxCandidate) === 1 ? $fluxCandidate : $fallback;
    }

    if (! $useLucide) {
        if (! \Flux\Flux::componentExists('icon.'.$fluxCandidate)) {
            $fluxCandidate = $fallback;
        }

        if (! \Flux\Flux::componentExists('icon.'.$fluxCandidate)) {
            $fluxCandidate = 'tag';
        }
    }
@endphp

@if ($useLucide)
    @php
        $lucideSvg = null;

        try {
            $lucideSvg = svg(
                'lucide-'.$lucideSlug,
                (string) $attributes->get('class', ''),
                $attributes->except('class')->getAttributes(),
            );
        } catch (\BladeUI\Icons\Exceptions\SvgNotFound) {
            $lucideSvg = null;
        }
    @endphp
    @if ($lucideSvg)
        {!! $lucideSvg->toHtml() !!}
    @else
        <flux:icon :icon="$fallback" {{ $attributes }} />
    @endif
@else
    <flux:icon :icon="$fluxCandidate" {{ $attributes }} />
@endif
