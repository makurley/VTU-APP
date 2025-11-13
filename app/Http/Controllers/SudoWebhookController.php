<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SudoCardTransaction;

class SudoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $event = $request->event;

        if ($event === 'card_transaction') {
            SudoCardTransaction::create($request->data);
        }

        return response()->json(['status' => 'success']);
    }
    
    // SudoWebhookController.php
public function handle(Request $request)
{
    $event = $request->event;
    
    // Check if the event is a card transaction
    if ($event === 'card_transaction') {
        SudoCardTransaction::create([
            'card_id' => $request->data['card_id'],
            'transaction_id' => $request->data['transaction_id'],
            'amount' => $request->data['amount'],
            'status' => $request->data['status'],
            // Add any other data as needed
        ]);
    }

    // Handle other events if needed

    return response()->json(['status' => 'success']);
}

}
