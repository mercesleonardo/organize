<?php

namespace App\Actions;

use App\Data\ReplyToTicketData;
use App\Notifications\TicketRepliedNotification;
use Illuminate\Support\Facades\DB;

final class ReplyToTicketAction
{
    public function execute(ReplyToTicketData $data): void
    {
        DB::transaction(function () use ($data): void {
            $data->ticket->update([
                'reply'  => $data->reply,
                'status' => $data->status,
            ]);

            $data->ticket->refresh();

            $owner = $data->ticket->user;

            if ($owner !== null) {
                $owner->notify(new TicketRepliedNotification($data->ticket));
            }
        });
    }
}
