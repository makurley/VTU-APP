<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SudoCardController extends Controller
{
   public function createCustomer(Request $request)
{
    $response = app(SudoService::class)->createCustomer([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
        'phone' => $request->phone,
        'metadata' => ['user_id' => auth()->id()]
    ]);

    // Save to DB and return view or redirect
}

public function issueCard($customerRef)
{
    $response = app(SudoService::class)->issueCard($customerRef);
    // Save card details to DB
}
  public function create()
    {
        return view('user.cards.create'); // Create this blade file next
    }


}
