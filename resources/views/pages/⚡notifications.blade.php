<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Notifications')] class extends Component
{
    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function markRead(string $id): void
    {
        $notification = Auth::user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Illuminate\Notifications\DatabaseNotification>
     */
    #[Computed]
    public function items()
    {
        return Auth::user()
            ->notifications()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Notifications') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Recent messages for your account.') }}</flux:text>
        </div>
        @if (auth()->user()->unreadNotifications()->exists())
            <flux:button type="button" variant="ghost" size="sm" wire:click="markAllRead">
                {{ __('Mark all as read') }}
            </flux:button>
        @endif
    </div>

    <div class="flex flex-col gap-3">
        @forelse ($this->items as $notification)
            <div
                wire:key="notif-{{ $notification->id }}"
                class="flex flex-col gap-2 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800"
            >
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <flux:heading size="sm">
                        @if ($notification->type === \App\Notifications\TicketRepliedNotification::class)
                            {{ __('Ticket replied') }}
                        @else
                            {{ class_basename($notification->type) }}
                        @endif
                    </flux:heading>
                    @if ($notification->read_at === null)
                        <flux:badge size="sm" color="amber">{{ __('New') }}</flux:badge>
                    @endif
                </div>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                    {{ $notification->data['body'] ?? __('You have a new notification.') }}
                </flux:text>
                @if (isset($notification->data['subject']))
                    <flux:text class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                        {{ $notification->data['subject'] }}
                    </flux:text>
                @endif
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <flux:text class="text-xs text-zinc-500">{{ $notification->created_at->format('d/m/Y H:i') }}</flux:text>
                    @if ($notification->read_at === null)
                        <flux:button type="button" variant="ghost" size="sm" wire:click="markRead('{{ $notification->id }}')">
                            {{ __('Mark as read') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        @empty
            <flux:text class="text-sm text-zinc-500">{{ __('No notifications yet.') }}</flux:text>
        @endforelse
    </div>
</div>
