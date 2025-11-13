<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CryptoTransaction;
use App\Models\User;

class AdminTransactionController extends Controller

{
    // Fetch and display all pending transactions with related user details
    public function index()
    {
        $transactions = CryptoTransaction::with('user') // Eager load the user relation
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.admin-transactions', compact('transactions'));
    }

    // Approve the transaction
    public function approve($id)
    {
        $transaction = CryptoTransaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaction already processed.');
        }

        $transaction->update([
            'status' => 'approved',
            'updated_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Transaction approved successfully!');
    }

    // Reject the transaction and refund the user
    public function reject($id)
    {
        $transaction = CryptoTransaction::findOrFail($id);
        $user = User::findOrFail($transaction->user_id);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaction already processed.');
        }

        // Refund user's wallet balance
        $user->increment('balance', $transaction->fiat_amount);

        $transaction->update([
            'status' => 'rejected',
            'updated_at' => Carbon::now(),
        ]);

        return back()->with('error', 'Transaction rejected and funds refunded.');
    }
}
