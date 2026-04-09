<?php

namespace App\Http\Controllers\Support;

use App\Actions\CreateTicketAction;
use App\Data\CreateTicketData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class TicketController extends Controller
{
    public function store(StoreTicketRequest $request, CreateTicketAction $action): RedirectResponse
    {
        try {
            $action->execute(new CreateTicketData(
                user: $request->user(),
                subject: (string) $request->validated('subject'),
                message: (string) $request->validated('message'),
            ));
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'subject' => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'Chamado enviado com sucesso. Em breve nossa equipe responderá.');
    }
}
