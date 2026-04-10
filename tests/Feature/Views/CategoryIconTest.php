<?php

use Illuminate\Support\Facades\Blade;

test('category-icon renderiza SVG Lucide com prefixo lucide:', function () {
    $html = Blade::render('<x-category-icon name="lucide:carrot" class="size-4" />');

    expect($html)->toContain('<svg')
        ->and($html)->toContain('size-4');
});

test('category-icon recua para Flux quando o ícone Lucide não existe', function () {
    $html = Blade::render('<x-category-icon name="lucide:icon-que-nao-existe-xyz" class="size-4" />');

    expect($html)->toContain('<svg');
});

test('category-icon usa Flux para nome simples', function () {
    $html = Blade::render('<x-category-icon name="home" class="size-4" />');

    expect($html)->toContain('<svg');
});
