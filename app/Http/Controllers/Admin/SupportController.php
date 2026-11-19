<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::query()->latest()->paginate(admin_listing_per_page())->withQueryString();

        return view('admin.support.index', compact('tickets'));
    }

    public function show(SupportTicket $support)
    {
        $support->load('messages');

        return view('admin.support.show', compact('support'));
    }

    public function reply(Request $request, SupportTicket $support)
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);

        SupportMessage::query()->create([
            'support_ticket_id' => $support->id,
            'sender_type' => 'admin',
            'sender_id' => auth('admin')->id(),
            'message' => $data['message'],
        ]);

        return back()->with('success', 'Reply sent.');
    }
}
