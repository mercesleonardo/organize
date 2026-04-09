<?php

use App\Enums\TicketStatus;
use App\Models\{Ticket, User};

test('usuário autenticado pode enviar um chamado', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->post(route('support.tickets.store'), [
        'subject' => 'Dúvida sobre o app',
        'message' => 'Olá, preciso de ajuda com uma informação.',
    ])->assertRedirect(route('support.contact'));

    expect(Ticket::query()->whereBelongsTo($user)->count())->toBe(1);

    $ticket = Ticket::query()->whereBelongsTo($user)->firstOrFail();

    expect($ticket->status)->toBe(TicketStatus::Open)
        ->and($ticket->reply)->toBeNull();
});

test('usuário não pode ter mais de 3 tickets abertos', function () {
    $user = User::factory()->create();

    Ticket::factory()->count(3)->create([
        'user_id' => $user->id,
        'status'  => TicketStatus::Open,
    ]);

    $this->actingAs($user);

    $this->from(route('support.contact'))
        ->post(route('support.tickets.store'), [
            'subject' => 'Novo ticket',
            'message' => 'Mensagem',
        ])
        ->assertRedirect(route('support.contact'))
        ->assertSessionHasErrors(['subject']);

    expect(Ticket::query()->whereBelongsTo($user)->count())->toBe(3);
});

test('rota de envio aplica rate limiting', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('support.tickets.store'), [
            'subject' => "Assunto {$i}",
            'message' => 'Mensagem',
        ])->assertRedirect();
    }

    $this->post(route('support.tickets.store'), [
        'subject' => 'Assunto 6',
        'message' => 'Mensagem',
    ])->assertStatus(429);
});
