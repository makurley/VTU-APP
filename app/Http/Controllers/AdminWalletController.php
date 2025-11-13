<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletAddress;

class AdminWalletController extends Controller
{
    public function index()
    {
        $wallets = WalletAddress::all();
        return view('admin.wallets', compact('wallets'));
    }

    public function update(Request $request)
    {
        foreach ($request->wallets as $crypto => $walletAddress) {
            WalletAddress::updateOrCreate(
                ['crypto_type' => $crypto],
                ['wallet_address' => $walletAddress]
            );
        }

        return back()->with('success', 'Wallet addresses updated successfully!');
    }
}
