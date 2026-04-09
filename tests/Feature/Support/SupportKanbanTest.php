<?php

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Livewire;

test('usuário comum não acessa o painel de suporte via rota', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::USER]));

    $this->get(route('support.kanban'))->assertForbidden();
});

test('suporte acessa o painel de suporte', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::SUPPORT]));

    $this->get(route('support.kanban'))->assertOk();
});

test('admin acessa o painel de suporte', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]));

    $this->get(route('support.kanban'))->assertOk();
});

test('usuário comum não monta o componente kanban', function () {
    $user = User::factory()->create(['role' => UserRole::USER]);

    $this->actingAs($user);

    Livewire::test('pages::support.kanban')->assertForbidden();
});
