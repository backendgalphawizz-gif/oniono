<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::query()
            ->where('creator_type', 'restaurant_user')
            ->where('creator_id', auth('restaurant')->id())
            ->latest()
            ->paginate(20);

        return view('restaurant.support.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $priorities = array_keys(config('restaurant_help.ticket_priorities', ['medium' => 'Medium']));

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'priority' => ['nullable', 'string', 'in:'.implode(',', $priorities)],
        ]);

        $ticket = SupportTicket::query()->create([
            'ticket_number' => 'TKT'.now()->format('ymdHis').random_int(100, 999),
            'creator_type' => 'restaurant_user',
            'creator_id' => auth('restaurant')->id(),
            'subject' => $data['subject'],
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'open',
        ]);

        SupportMessage::query()->create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => 'restaurant_user',
            'sender_id' => auth('restaurant')->id(),
            'message' => $data['message'],
        ]);

        return redirect()->route('restaurant.support.show', $ticket)->with('success', 'Ticket created.');
    }

    public function show(SupportTicket $support)
    {
        abort_if($support->creator_type !== 'restaurant_user' || $support->creator_id !== auth('restaurant')->id(), 403);
        $support->load('messages');

        return view('restaurant.support.show', compact('support'));
    }

    public function reply(Request $request, SupportTicket $support)
    {
        abort_if($support->creator_type !== 'restaurant_user' || $support->creator_id !== auth('restaurant')->id(), 403);

        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);

        SupportMessage::query()->create([
            'support_ticket_id' => $support->id,
            'sender_type' => 'restaurant_user',
            'sender_id' => auth('restaurant')->id(),
            'message' => $data['message'],
        ]);

        if ($support->status === 'closed') {
            $support->update(['status' => 'open']);
        }

        return back()->with('success', 'Reply sent.');
    }
}
