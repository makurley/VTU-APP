<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction; // Make sure this model exists

class InvoiceController extends Controller
{
    public function show($id)
    {
        // Retrieve the transaction details based on ID
        $transaction = Transaction::findOrFail($id);

        return view('invoice', compact('transaction'));
    }

    public function store(Request $request)
    {
        // Redirect to the invoice page with the transaction ID
        return redirect()->route('invoice.show', ['id' => $request->id]);
    }
}
