<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SudoCard;
use App\Models\SudoCardTransaction;
use Auth;

class SudoController extends Controller
{
   public function createCustomer(Request $request, SudoService $sudo)
{
    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '+234xxxxxxxxx',
    ];

    $response = $sudo->createCustomer($data);

    // Store result in your `sudo_customers` table
    return response()->json($response);
}
public function createCard(Request $request)
{
    $customerId = auth()->user()->sudo_customer_id;

    $response = Http::withToken(config('services.sudo.secret_key'))
        ->post('https://api.sudo.africa/cards', [
            'customer_id' => $customerId,
            'currency' => 'NGN',
            'amount' => 0,
        ]);

    if ($response->successful()) {
        // Save card info
        $data = $response->json()['data'];
        SudoCard::create([
            'user_id' => auth()->id(),
            'sudo_card_id' => $data['id'],
            'last4' => $data['last4'],
            'card_type' => $data['card_type'],
            'status' => $data['status'],
        ]);

        return back()->with('success', 'Card created successfully');
    }

    return back()->with('error', 'Failed to create card');
}
public function toggleCardStatus($cardId, $action)
{
    $url = "https://api.sudo.africa/cards/{$cardId}/" . ($action === 'freeze' ? 'freeze' : 'unfreeze');

    $response = Http::withToken(config('services.sudo.secret_key'))
        ->post($url);

    if ($response->successful()) {
        SudoCard::where('sudo_card_id', $cardId)->update(['status' => $action === 'freeze' ? 'inactive' : 'active']);
        return back()->with('success', 'Card status updated');
    }

    return back()->with('error', 'Failed to update card status');
}
public function terminateCard($cardId)
{
    $response = Http::withToken(config('services.sudo.secret_key'))
        ->delete("https://api.sudo.africa/cards/{$cardId}");

    if ($response->successful()) {
        SudoCard::where('sudo_card_id', $cardId)->delete();
        return back()->with('success', 'Card terminated');
    }

    return back()->with('error', 'Failed to terminate card');
}
// public function fetchCardTransactions($cardId)
// {
//     $response = Http::withToken(config('services.sudo.secret_key'))
//         ->get("https://api.sudo.africa/cards/{$cardId}/transactions");

//     if ($response->successful()) {
//         return view('user.card-transactions', ['transactions' => $response->json()['data']]);
//     }

//     return back()->with('error', 'Failed to retrieve transactions');
// }
public function completeKyc(Request $request)
{
    $user = auth()->user();
    $response = Http::withToken(config('services.sudo.secret_key'))
        ->put("https://api.sudo.africa/customers/{$user->sudo_customer_id}", [
            'metadata' => [
                'nin' => $request->nin,
                'bvn' => $request->bvn,
                'dob' => $request->dob,
            ]
        ]);

    return $response->successful()
        ? back()->with('success', 'KYC updated')
        : back()->with('error', 'KYC failed');
}

public function fetchCardTransactions($cardId)
{
    $transactions = SudoCardTransaction::where('card_id', $cardId)->get();

    if ($transactions->isEmpty()) {
        \Log::info("No transactions found for card_id: $cardId");
    }
return view('user.card-transactions', compact('transactions'));
}

public function indexCardTransactions()
{
    $user = auth()->user();

    $cards = SudoCard::where('user_id', $user->id)->get();

    $transactions = []; // Use 'transactions' instead of 'allTransactions'

    foreach ($cards as $card) {
        $response = Http::withToken(config('services.sudo.secret_key'))
            ->get("https://api.sudo.africa/cards/{$card->sudo_card_id}/transactions");

        if ($response->successful()) {
            $transactions[$card->last4] = $response->json()['data'];
        }
    }

    return view('user.card-transactions', compact('transactions'));
}

public function dashboard()
{
    // Fetch the user's card details (if available)
    $card = SudoCard::where('user_id', auth()->id())->first();
    $cardId = $card ? $card->sudo_card_id : null;

    // Pass $cardId to the view
    return view('user.dashboard', compact('cardId'));
}
public function viewCards()
{
    $cards = SudoCard::with('user')->latest()->get(); // optional: eager load user
    return view('admin.cards.index', compact('cards'));
}
}
