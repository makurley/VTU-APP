<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CryptoTransaction;
use App\Models\User;
use App\Models\WalletAddress;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CryptoController extends Controller
{
    // Show the form for buying crypto
    public function showBuyForm()
    {
        // Fetch available wallet addresses from the database
        $walletAddresses = WalletAddress::all();

        // Return the form view with wallet addresses
        return view('buy-crypto', compact('walletAddresses'));
    }

    // Process the purchase of crypto
 public function processPurchase(Request $request)
{
    $request->validate([
        'crypto_type' => 'required|string',
        'crypto_amount' => 'required|numeric|min:0.0001',
        'wallet_address' => 'required|string',
        'buyer_wallet_address' => 'required|string',
        'fiat_amount' => 'required|numeric|min:1', // Fiat amount must be submitted from frontend
    ]);

    // Define transaction charge (e.g., 2%)
    $transactionChargePercent = 2;

    // Get fiat amount from frontend
    $fiatAmount = $request->fiat_amount;

    // Calculate transaction charge
    $transactionCharge = ($transactionChargePercent / 100) * $fiatAmount;
    $totalAmountToDeduct = $fiatAmount + $transactionCharge;

    // Get the authenticated user
    $user = auth()->user();

    // Log the user's current balance to help with debugging
    \Log::info("User Balance: " . $user->balance);  // Use the correct column name here

    // Check if user has enough balance
    if ($user->balance < $totalAmountToDeduct) {
        return back()->with('error', 'Insufficient balance.');
    }

    // Deduct total amount from user wallet balance
    $user->balance -= $totalAmountToDeduct;
    $user->save();

    // Log the updated wallet balance
    \Log::info("Updated Wallet Balance: " . $user->balance);  // Ensure this is correct

    // Store transaction in the database
    $transaction = new CryptoTransaction();
    $transaction->user_id = $user->id;
    $transaction->crypto_type = $request->crypto_type;
    $transaction->crypto_amount = $request->crypto_amount;
    $transaction->fiat_amount = $fiatAmount;
    $transaction->transaction_charge = $transactionCharge;
    $transaction->wallet_address = $request->wallet_address;
    $transaction->buyer_wallet_address = $request->buyer_wallet_address;
    $transaction->status = 'pending'; // Admin approval required
    $transaction->save();

    return redirect()->route('crypto.user.transactions')->with('success', 'Crypto purchase request submitted.');
}


    // Display the user's crypto transactions
    public function userTransactions()
    {
        $transactions = Auth::user()->cryptoTransactions()->latest()->get();
        return view('user.crypto-transactions', compact('transactions'));
    }

    // Display the admin's view of all pending crypto transactions
    public function adminTransactions()
    {
        $transactions = CryptoTransaction::where('status', 'pending')->latest()->get();
        return view('admin.admin-transactions', compact('transactions'));
    }

    // Get the wallet address for a specific crypto type
    public function getWalletAddress($crypto)
    {
        \Log::info("Fetching wallet address for crypto: " . $crypto);

        $wallets = WalletAddress::where('crypto_type', $crypto)->pluck('wallet_address');

        if ($wallets->isNotEmpty()) {
            \Log::info("Wallet addresses found: " . $wallets->join(', '));
            return response()->json(['wallet_addresses' => $wallets]);
        } else {
            \Log::info("No wallet addresses found for crypto: " . $crypto);
            return response()->json(['wallet_addresses' => []]);
        }
    }

    // Admin function to approve a transaction
    public function approveTransaction($id)
    {
        $transaction = CryptoTransaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaction already processed.');
        }

        $transaction->update(['status' => 'approved', 'updated_at' => Carbon::now()]);
        return back()->with('success', 'Transaction approved.');
    }

    // Admin function to reject a transaction
    public function rejectTransaction($id)
    {
        $transaction = CryptoTransaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaction already processed.');
        }

        // Refund the user
        $transaction->user->increment('balance', $transaction->fiat_amount);
        $transaction->update(['status' => 'rejected', 'updated_at' => Carbon::now()]);
        
        return back()->with('success', 'Transaction rejected and funds refunded.');
    }
    
public function history()
    {
        $transactions = CryptoTransaction::latest()->get();
        return view('admin.crypto.crpto-history', compact('transactions'));
    }

}
