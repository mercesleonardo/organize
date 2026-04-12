<x-mail::message>
# {{ __('Your question was answered') }}

{{ __('Hello :name,', ['name' => $userName]) }}

{{ __('The support team replied to your ticket.') }} **{{ $ticket->subject }}**

<x-mail::panel>
{!! nl2br(e($ticket->reply)) !!}
</x-mail::panel>

<x-mail::button :url="$dashboardUrl">
{{ __('View in the app') }}
</x-mail::button>

{{ __('Thanks for using :app.', ['app' => config('app.name')]) }}
</x-mail::message>
