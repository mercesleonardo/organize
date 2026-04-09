<?php

use App\Actions\ReplyToTicketAction;
use App\Data\ReplyToTicketData;
use App\Enums\TicketStatus;
use App\Http\Requests\ReplyToTicketRequest;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Suporte — Chamados')] class extends Component
{
    public bool $showModal = false;

    public ?int $editingTicketId = null;

    public string $ticketReply = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Ticket::class);
    }

    public function updatedShowModal(bool $value): void
    {
        if ($value === false) {
            $this->editingTicketId = null;
            $this->ticketReply = '';
        }
    }

    #[Computed]
    public function editingTicket(): ?Ticket
    {
        if ($this->editingTicketId === null) {
            return null;
        }

        return Ticket::query()->with('user')->find($this->editingTicketId);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Ticket>
     */
    #[Computed]
    public function openTickets()
    {
        return Ticket::query()
            ->with('user')
            ->where('status', TicketStatus::Open)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Ticket>
     */
    #[Computed]
    public function inProgressTickets()
    {
        return Ticket::query()
            ->with('user')
            ->where('status', TicketStatus::InProgress)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Ticket>
     */
    #[Computed]
    public function resolvedTickets()
    {
        return Ticket::query()
            ->with('user')
            ->where('status', TicketStatus::Resolved)
            ->orderByDesc('id')
            ->limit(40)
            ->get();
    }

    public function openTicket(int $id): void
    {
        $ticket = Ticket::query()->findOrFail($id);
        $this->authorize('update', $ticket);

        $this->editingTicketId = $id;
        $this->ticketReply = (string) ($ticket->reply ?? '');
        $this->showModal = true;
    }

    public function saveInProgress(ReplyToTicketAction $action): void
    {
        if ($this->editingTicketId === null) {
            return;
        }

        $ticket = Ticket::query()->with('user')->findOrFail($this->editingTicketId);

        $this->authorize('update', $ticket);

        $validated = Validator::make(
            ['ticketReply' => $this->ticketReply],
            ReplyToTicketRequest::ticketReplyRules(),
        )->validate();

        $action->execute(new ReplyToTicketData(
            agent: Auth::user(),
            ticket: $ticket,
            reply: trim((string) $validated['ticketReply']),
            status: TicketStatus::InProgress,
        ));

        $this->dispatch('$refresh');
        $this->showModal = false;
        $this->editingTicketId = null;
        $this->ticketReply = '';
        session()->flash('status', 'Resposta salva e chamado marcado como em andamento.');
    }

    public function saveResolved(ReplyToTicketAction $action): void
    {
        if ($this->editingTicketId === null) {
            return;
        }

        $ticket = Ticket::query()->with('user')->findOrFail($this->editingTicketId);

        $this->authorize('update', $ticket);

        $validated = Validator::make(
            ['ticketReply' => $this->ticketReply],
            ReplyToTicketRequest::ticketReplyRules(),
        )->validate();

        $action->execute(new ReplyToTicketData(
            agent: Auth::user(),
            ticket: $ticket,
            reply: trim((string) $validated['ticketReply']),
            status: TicketStatus::Resolved,
        ));

        $this->dispatch('$refresh');
        $this->showModal = false;
        $this->editingTicketId = null;
        $this->ticketReply = '';
        session()->flash('status', 'Chamado respondido e marcado como resolvido.');
    }
}; ?>

<div class="flex flex-col gap-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <flux:heading size="xl">Painel de atendimento</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Chamados organizados por status. Clique em um card para responder.</flux:text>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success">
            {{ session('status') }}
        </flux:callout>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex min-h-[12rem] flex-col gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700 dark:bg-zinc-900/40">
            <flux:heading size="md">Aberto</flux:heading>
            <flux:text class="text-sm text-zinc-500">{{ $this->openTickets->count() }} chamado(s)</flux:text>
            <div class="flex flex-1 flex-col gap-2 overflow-y-auto">
                @forelse ($this->openTickets as $ticket)
                    <button
                        type="button"
                        wire:key="kanban-open-{{ $ticket->id }}"
                        wire:click="openTicket({{ $ticket->id }})"
                        class="w-full rounded-xl border border-zinc-200 bg-white p-3 text-start shadow-sm transition hover:border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:hover:border-zinc-500"
                    >
                        <flux:heading size="sm" class="line-clamp-2">{{ $ticket->subject }}</flux:heading>
                        <flux:text class="mt-1 text-xs text-zinc-500">{{ $ticket->user?->name ?? '—' }}</flux:text>
                        <flux:text class="mt-1 line-clamp-2 text-xs text-zinc-600 dark:text-zinc-300">{{ $ticket->message }}</flux:text>
                    </button>
                @empty
                    <flux:text class="text-sm text-zinc-500">Nenhum chamado aberto.</flux:text>
                @endforelse
            </div>
        </div>

        <div class="flex min-h-[12rem] flex-col gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700 dark:bg-zinc-900/40">
            <flux:heading size="md">Em andamento</flux:heading>
            <flux:text class="text-sm text-zinc-500">{{ $this->inProgressTickets->count() }} chamado(s)</flux:text>
            <div class="flex flex-1 flex-col gap-2 overflow-y-auto">
                @forelse ($this->inProgressTickets as $ticket)
                    <button
                        type="button"
                        wire:key="kanban-progress-{{ $ticket->id }}"
                        wire:click="openTicket({{ $ticket->id }})"
                        class="w-full rounded-xl border border-amber-200/80 bg-white p-3 text-start shadow-sm transition hover:border-amber-300 dark:border-amber-900/50 dark:bg-zinc-800 dark:hover:border-amber-800/60"
                    >
                        <flux:heading size="sm" class="line-clamp-2">{{ $ticket->subject }}</flux:heading>
                        <flux:text class="mt-1 text-xs text-zinc-500">{{ $ticket->user?->name ?? '—' }}</flux:text>
                        <flux:text class="mt-1 line-clamp-2 text-xs text-zinc-600 dark:text-zinc-300">{{ $ticket->message }}</flux:text>
                    </button>
                @empty
                    <flux:text class="text-sm text-zinc-500">Nenhum chamado em andamento.</flux:text>
                @endforelse
            </div>
        </div>

        <div class="flex min-h-[12rem] flex-col gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700 dark:bg-zinc-900/40">
            <flux:heading size="md">Resolvido</flux:heading>
            <flux:text class="text-sm text-zinc-500">Últimos {{ $this->resolvedTickets->count() }}</flux:text>
            <div class="flex flex-1 flex-col gap-2 overflow-y-auto">
                @forelse ($this->resolvedTickets as $ticket)
                    <button
                        type="button"
                        wire:key="kanban-resolved-{{ $ticket->id }}"
                        wire:click="openTicket({{ $ticket->id }})"
                        class="w-full rounded-xl border border-emerald-200/80 bg-white p-3 text-start shadow-sm transition hover:border-emerald-300 dark:border-emerald-900/40 dark:bg-zinc-800 dark:hover:border-emerald-800/50"
                    >
                        <flux:heading size="sm" class="line-clamp-2">{{ $ticket->subject }}</flux:heading>
                        <flux:text class="mt-1 text-xs text-zinc-500">{{ $ticket->user?->name ?? '—' }}</flux:text>
                    </button>
                @empty
                    <flux:text class="text-sm text-zinc-500">Nenhum chamado resolvido ainda.</flux:text>
                @endforelse
            </div>
        </div>
    </div>

    <flux:modal wire:model="showModal" class="md:w-lg">
        @if ($this->editingTicket)
            <flux:heading size="lg">{{ $this->editingTicket->subject }}</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-500">
                {{ $this->editingTicket->user?->name ?? '—' }} · {{ $this->editingTicket->created_at->format('d/m/Y H:i') }}
            </flux:text>

            <div class="mt-4 rounded-xl bg-zinc-50 p-3 text-sm text-zinc-800 dark:bg-zinc-900/50 dark:text-zinc-200">
                <div class="mb-1 text-xs font-medium uppercase text-zinc-500">Mensagem do usuário</div>
                <div class="whitespace-pre-wrap">{{ $this->editingTicket->message }}</div>
            </div>

            <div class="mt-4 flex flex-col gap-2">
                <flux:field>
                    <flux:label>Resposta do suporte</flux:label>
                    <textarea
                        wire:model="ticketReply"
                        rows="6"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-xs focus:border-zinc-400 focus:outline-hidden focus:ring-2 focus:ring-zinc-400/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500"
                    >{{ $ticketReply }}</textarea>
                    <flux:error name="ticketReply" />
                </flux:field>
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Fechar</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="filled" wire:click="saveInProgress" wire:loading.attr="disabled">
                    Salvar e em andamento
                </flux:button>
                <flux:button type="button" variant="primary" wire:click="saveResolved" wire:loading.attr="disabled">
                    Salvar e resolver
                </flux:button>
            </div>
        @endif
    </flux:modal>
</div>
