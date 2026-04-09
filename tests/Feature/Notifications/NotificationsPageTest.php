<?php

use App\Models\User;

test('usuário autenticado acessa a página de notificações', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('notifications.index'))->assertOk();
});
