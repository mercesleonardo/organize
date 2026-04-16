<?php

use App\Actions\ReplyToTicketAction;
use App\Data\ReplyToTicketData;
use App\Enums\{TicketStatus, UserRole};
use App\Models\{Ticket, User};
use App\Notifications\TicketRepliedNotification;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('usuário define idioma preferencial para notificações', function () {
    $withLocale = User::factory()->create(['locale' => 'pt_BR']);
    expect($withLocale)->toBeInstanceOf(HasLocalePreference::class)
        ->and($withLocale->preferredLocale())->toBe('pt_BR');

    $defaultLocale = User::factory()->create(['locale' => null]);
    config(['app.locale' => 'en']);

    expect($defaultLocale->preferredLocale())->toBe('en');
});

test('action de resposta envia notificação ao dono do chamado pelos canais database e mail', function () {
    Notification::fake();

    $owner   = User::factory()->create(['role' => UserRole::USER]);
    $support = User::factory()->create(['role' => UserRole::SUPPORT]);

    $ticket = Ticket::factory()->create([
        'user_id' => $owner->id,
        'status'  => TicketStatus::Open,
        'subject' => 'Problema',
        'message' => 'Preciso de ajuda',
    ]);

    $this->actingAs($support);

    app(ReplyToTicketAction::class)->execute(new ReplyToTicketData(
        agent: $support,
        ticket: $ticket,
        reply: 'Resposta do suporte.',
        status: TicketStatus::Resolved,
    ));

    Notification::assertSentTo(
        $owner,
        TicketRepliedNotification::class,
        function (TicketRepliedNotification $notification, array $channels): bool {
            return in_array('mail', $channels, true) && in_array('database', $channels, true);
        }
    );
});

test('kanban salvar e resolver dispara notificação ao usuário', function () {
    Notification::fake();

    $owner   = User::factory()->create(['role' => UserRole::USER]);
    $support = User::factory()->create(['role' => UserRole::SUPPORT]);

    $ticket = Ticket::factory()->create([
        'user_id' => $owner->id,
        'status'  => TicketStatus::Open,
    ]);

    $this->actingAs($support);

    Livewire::test('pages::support.kanban')
        ->call('openTicket', $ticket->id)
        ->set('ticketReply', 'Segue orientação.')
        ->call('saveResolved')
        ->assertHasNoErrors();

    Notification::assertSentTo($owner, TicketRepliedNotification::class);

    expect($ticket->fresh()->status)->toBe(TicketStatus::Resolved)
        ->and($ticket->fresh()->reply)->toBe('Segue orientação.');
});
