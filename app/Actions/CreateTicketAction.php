<?php

namespace App\Actions;

use App\Data\CreateTicketData;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CreateTicketAction
{
    public function execute(CreateTicketData $data): Ticket
    {
        return DB::transaction(function () use ($data): Ticket {
            $openCount = Ticket::query()
                ->whereBelongsTo($data->user)
                ->where('status', TicketStatus::Open)
                ->count();

            if ($openCount >= 3) {
                throw new InvalidArgumentException(__('You already have 3 open tickets. Please wait for a support reply before opening another one.'));
            }

            return Ticket::query()->create([
                'user_id' => $data->user->id,
                'subject' => $data->subject,
                'message' => $data->message,
                'status'  => TicketStatus::Open,
                'reply'   => null,
            ]);
        });
    }
}
