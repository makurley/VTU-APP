<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    Achiever,
    Legal,
    SupportTicket,TicketComment
};
use Str;use Auth;
class SupportController extends Controller
{
    //
    
    public function tickets()
    {
        $title = "All Support Tickets";
        $tickets = SupportTicket::orderByDesc('status')->get();
        return view('admin.support.tickets', compact('title','tickets'));
    }
    public function unread_tickets()
    {
        $title = "Unread Tickets";
        $tickets = SupportTicket::whereStatus('1')->orderByDesc('id')->get();
        return view('admin.support.tickets', compact('title','tickets'));
    }
    // delete ticket
    public function delete($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->comments()->delete();
        $ticket->delete();
        return redirect()->back()->withSuccess('Ticket has been deleted');
    }
    // reply ticket
    public function reply($id)
    {
        $ticket = SupportTicket::with(['comments'])->findOrFail($id);
        $title = 'Ticket #' . $ticket->ticket;
        return view('admin.support.details', compact('title', 'ticket'));
    }
    // save reply
    public function comment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|max:500'
        ]);
        $ticket = SupportTicket::findOrFail($id);
        $ticket->comments()->save(new TicketComment([
            'comment' => $request->comment,
            'type' => 1
        ]));

        if ($ticket->status == 0) {
            $ticket->update([
                'status' => 1
            ]);
        }
        // send email
        $subj = "Ticket #{$ticket->ticket} Replied by Admin";
        $mesg = $request->comment;
        general_email($ticket->user->email, $mesg, $subj);
        return back()->withSuccess('Ticket has been replied');
    }

    
    // user tickets
    public function user_tickets()
    {
        $tickets = Auth::user()->tickets()->orderByDesc('status')->get();
        return view('user.ticket.index', compact('tickets'));
    }
    public function close_ticket($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->update([
            'status' => 0
        ]);
       
        return redirect()->back()->withSuccess('Ticket has been closed');
    }
    function ticket_detail($id,$slug)
    {
        $ticket = Auth::user()->tickets()->with(['comments'])->findOrFail($id);
        return view('user.ticket.detail', compact('ticket'));
    }
    public function user_comment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|max:500'
        ]);
        $ticket = SupportTicket::findOrFail($id);
        $ticket->comments()->save(new TicketComment([
            'comment' => $request->comment,
            'type' => 0
        ]));
        if ($ticket->status == 0) {
            $ticket->update([
                'status' => 1
            ]);
        }

        // send email
        $subj = "Ticket #{$ticket->ticket} Replied by {$ticket->user->username}";
        $mesg = $request->comment;
        general_email(get_setting('email'), $mesg, $subj);

        return back()->withSuccess('Ticket has been replied');
    }
    function new_ticket()
    {
        return view('user.ticket.new');
    }
    public function create_ticket(Request $request)
    {
        $request->validate([
            'subject' => 'required',
            'message' => 'required',
        ]);

        $ticket = Auth::user()->tickets()->save(new SupportTicket([
            'ticket' => getTrx(8),
            'subject' => $request->subject,
        ]));

        $ticket->comments()->save(new TicketComment([
            'comment' => $request->message,
        ]));
        // send email
        $subj = "Ticket #{$ticket->ticket} Created By {$ticket->user->username}";
        $mesg = $request->message;
        general_email(get_setting('email'), $mesg, $subj);

        return redirect()->route('user.ticket.detail', [$ticket->id, slug($ticket->ticket)]);
    }
}
