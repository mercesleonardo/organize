<?php

use App\Models\User;

test('visitante pode definir idioma na sessão', function () {
    $response = $this->from('/')->post(route('locale.update'), ['locale' => 'pt_BR']);

    $response->assertRedirect('/')->assertSessionHas('locale', 'pt_BR');
});

test('idioma inválido é rejeitado', function () {
    $this->post(route('locale.update'), ['locale' => 'xx'])->assertSessionHasErrors('locale');
});

test('utilizador autenticado persiste o idioma na conta', function () {
    $user = User::factory()->create(['locale' => null]);

    $this->actingAs($user)
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect();

    expect($user->fresh()->locale)->toBe('en');
});
