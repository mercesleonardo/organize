<x-mail::message>
# Sua dúvida foi respondida

Olá **{{ $userName }}**,

O suporte respondeu ao chamado **{{ $ticket->subject }}**.

<x-mail::panel>
{!! nl2br(e($ticket->reply)) !!}
</x-mail::panel>

<x-mail::button :url="$dashboardUrl">
Ver no aplicativo
</x-mail::button>

Obrigado por usar o {{ config('app.name') }}.
</x-mail::message>
