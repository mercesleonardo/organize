<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public function getStatusMessageProperty(): ?string
    {
        return session('status');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Ticket>
     */
    #[Computed]
    public function tickets()
    {
        return Auth::user()
            ->tickets()
            ->orderByDesc('id')
            ->limit(10)
            ->get();
    }
};

?>

<div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
    <div class="flex flex-col gap-6">
        <div>
            <flux:heading size="lg">Fale Conosco</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Envie uma dúvida para o suporte e acompanhe as respostas.</flux:text>
        </div>

        @if ($this->statusMessage)
            <flux:callout variant="success">
                {{ $this->statusMessage }}
            </flux:callout>
        @endif

        <form method="POST" action="{{ route('support.tickets.store') }}" class="flex flex-col gap-4">
            @csrf

            <flux:field>
                <flux:label>Assunto</flux:label>
                <flux:input name="subject" :value="old('subject')" />
                <flux:error name="subject" />
            </flux:field>

            <flux:field>
                <flux:label>Mensagem</flux:label>
                <flux:textarea name="message" rows="5">{{ old('message') }}</flux:textarea>
                <flux:error name="message" />
            </flux:field>

            <div class="flex items-center justify-end gap-2">
                <flux:button type="submit" variant="primary">Enviar chamado</flux:button>
            </div>
        </form>

        <div class="border-t border-zinc-200 pt-6 dark:border-zinc-700">
            <div class="mb-3 flex items-center justify-between gap-2">
                <flux:heading size="md">Seus chamados</flux:heading>
                <flux:text class="text-sm text-zinc-500">Últimos 10</flux:text>
            </div>

            <div class="flex flex-col gap-3">
                @forelse ($this->tickets as $ticket)
                    <div wire:key="ticket-{{ $ticket->id }}" class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900/30">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:heading size="sm" class="truncate">{{ $ticket->subject }}</flux:heading>
                                    <flux:badge size="sm" inset="top bottom" color="zinc">
                                        {{ $ticket->status->label() }}
                                    </flux:badge>
                                </div>
                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    Enviado em {{ $ticket->created_at->format('d/m/Y H:i') }}
                                </flux:text>
                            </div>
                        </div>

                        <div class="mt-3 grid gap-3 md:grid-cols-2">
                            <div class="rounded-lg bg-zinc-50 p-3 text-sm text-zinc-700 dark:bg-zinc-900/50 dark:text-zinc-200">
                                <div class="mb-1 font-medium text-zinc-500">Sua mensagem</div>
                                <div class="whitespace-pre-wrap">{{ $ticket->message }}</div>
                            </div>

                            <div class="rounded-lg bg-zinc-50 p-3 text-sm text-zinc-700 dark:bg-zinc-900/50 dark:text-zinc-200">
                                <div class="mb-1 font-medium text-zinc-500">Resposta do suporte</div>
                                @if ($ticket->reply)
                                    <div class="whitespace-pre-wrap">{{ $ticket->reply }}</div>
                                @else
                                    <div class="text-zinc-500">Ainda não respondido.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700">
                        Você ainda não possui chamados.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

