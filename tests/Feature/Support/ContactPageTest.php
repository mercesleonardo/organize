<?php

use App\Models\User;
use Livewire\Livewire;

test('página fale conosco responde 200 para usuário autenticado', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('support.contact'))->assertOk();
});

test('componente fale conosco monta sem erro', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::support.ticket-center')->assertOk();
});
