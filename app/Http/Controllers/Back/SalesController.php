<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\{
    BetTrx,
    Bulksms,
    DecoderTrx,
    Deposit, EduTrx, Mdeposit,
    NetworkTrx,
    PowerTrx,
    RechargePin,
    SochiTrx,
    Transaction
};
use Illuminate\Http\Request;

class SalesController extends Controller
{
    //
    function deposits()
    {
        $deposits = Deposit::orderByDesc('id')->paginate(200);
        return \view('admin.reports.deposits', compact('deposits'));
    }
    function manual_deposits()
    {
        $deposits = Mdeposit::orderByDesc('id')->paginate(200);
        return \view('admin.reports.mdeposits', compact('deposits'));
    }
    public function manual_deposits_reject($id)
    {
        $mpayment = Mdeposit::findOrFail($id);
        $mpayment->status = 3;
        $mpayment->save();
        return back()->withSuccess("Payment has been rejected and deleted");

    }
    public function manual_deposits_delete($id)
    {
        $mpayment = Mdeposit::findOrFail($id);
        $mpayment->status = 3;
        $mpayment->save();
        return back()->withSuccess("Payment has been rejected and deleted");

    }
    public function manual_deposits_pay($id)
    {
        $mpayment = Mdeposit::findOrFail($id);
        $user = $mpayment->user;
        $mpayment->status = 1;
        $mpayment->save();
        $final = $mpayment['amount'] - sys_setting('bank_fee');
        // create deposit
        $deposit = new Deposit();
        $deposit->user_id = $user->id;
        $deposit->type = 'manual'; // 1- event, 2- form, 3-vote
        $deposit->gateway = "manual";
        $deposit->trx = $mpayment['code'];
        $deposit->message = $mpayment['message'];
        $deposit->amount = $final;
        $deposit->status = 1;
        $deposit->save();
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 1; // 1- credit, 2- debit, 3-others
        $trans->code = \getTrxcode(14);
        $trans->message = $mpayment['message'];
        $trans->amount = $final;
        $trans->status = 1;
        $trans->charge = 0;
        $trans->service = 9;
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance + $final;
        $trans->save();
        // Add User Balance
        $user->balance =  $trans['new_balance'];
        $user->save();

        if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
            \send_emails($user->email, 'DEPOSIT_EMAIL',
            [
                'username' => $user['username'],
                'amount' => \format_price($final),
                'method' => 'manual',
                'date' => $trans->created_at
            ]);
        }
        return back()->withSuccess("Payment Approved Successfully");

    }
    function transactions()
    {
        $transactions = Transaction::orderByDesc('updated_at')->simplePaginate(500);
        return \view('admin.reports.transaction', compact('transactions'));
    }
    function approve_transactions($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->status = 1;
        $transaction->save();
        // return $transaction;

        return back()->withSuccess("Transaction Approved Successfully.");
    }

    function reverse_transactions($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->status = 4;
        $transaction->message = "Reversed ". $transaction->message;
        $transaction->save();
        // return $transaction;
        // Add User Balance
        $user = $transaction->user;
        $user->balance =  $user->balance + $transaction['amount'];
        $user->save();
        // create transaction
        return back()->withSuccess("Transaction Reversed Successfully.");
    }

    // sales
    function airtime_sales()
    {
        $trx = NetworkTrx::whereType(1)->orderByDesc('id')->paginate(200);
        return view('admin.sales.airtime', compact('trx'));
    }
    function data_sales()
    {
        $trx = NetworkTrx::whereType(2)->orderByDesc('id')->paginate(200);
        return view('admin.sales.data', compact('trx'));
    }
    function airtime_swap()
    {
        $trx = NetworkTrx::whereType(3)->orderByDesc('id')->paginate(200);
        return view('admin.sales.swap', compact('trx'));
    }
    function airtime_swap_delete($id)
    {
        $trx = NetworkTrx::findOrFail($id);
        $trx->status = 3;
        $trx->save();
        return back()->withSuccess("Airtime swap cancelled successfully");
    }
    function airtime_swap_approve($id)
    {
        $trx = NetworkTrx::findOrFail($id);
        if($trx->status == 1 ||$trx->status == 3){
            return back()->withError("Something went wrong");
        }
        $trx->status = 1;
        $trx->save();
        $user = $trx->user;
        $fee = $trx->amount - $trx->charge;
         // create transaction
        $trans = new Transaction();
        $trans->user_id = $trx->user->id;
        $trans->type = 1; // 1- credit, 2- deit, 3-others
        $trans->code = getTrxcode(14);
        $trans->message = $trx->name;
        $trans->amount = $trx->amount - $trx->charge;
        $trans->status = 1;
        $trans->charge = $trx->charge;
        $trans->service = 3; // bills
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance + $fee;
        $trans->save();
        // add user balance
        $user->balance = $user->balance + $fee;
        $user->save();
        // send trxn email
        if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
            \send_emails($user->email, 'TRX_EMAIL',
            [
                'username' => $user['username'],
                'code' => $trans->code,
                'trx_details' => $trans->message,
                'trx_type' => trans_type2($trans->type),
                'amount' => format_price($trans['amount']),
                'date' => $trans->updated_at
            ]);
        }
        return back()->withSuccess("Airtime swap was successfully");
    }
    function power_sales()
    {
        $trx = PowerTrx::orderByDesc('id')->paginate(200);
        return view('admin.sales.power', compact('trx'));
    }
    function cable_sales()
    {
        $trx = DecoderTrx::orderByDesc('id')->paginate(200);
        return view('admin.sales.cable', compact('trx'));
    }
    function education_sales()
    {
        $trx = EduTrx::orderByDesc('id')->paginate(200);
        return view('admin.sales.education', compact('trx'));
    }

    function voucher_pins ()
    {
        $trx = RechargePin::orderByDesc('id')->paginate(200);
        return view('admin.sales.vouchers', compact('trx'));
    }
    function bulksms(){
        $trx = Bulksms::orderByDesc('updated_at')->paginate(100);
        return view('admin.sales.bulksms', compact('trx'));
    }
    function bet(){
        $trx = BetTrx::orderByDesc('updated_at')->paginate(100);
        return view('admin.sales.bet', compact('trx'));
    }
    function utility(){
        $trx = Transaction::whereIn('service', ['tax', 'translog', 'relinst'])->orderByDesc('id')->paginate(100);
        return view('admin.sales.utility', compact('trx'));
    }

    function giftcards (){
        $trx = SochiTrx::where('type', 'giftcard')->orderByDesc('id')->paginate(100);
        return view('admin.sales.giftcard', compact('trx'));
    }
    function intl_topup(){
        $title = "International Topup";
        $trx = SochiTrx::where('type', 'topup')->orderByDesc('id')->paginate(100);
        return view('admin.sales.topup', compact('trx','title'));
    }
    function intl_data(){
        $title = "International Data";
        $trx = SochiTrx::where('type', 'data')->orderByDesc('id')->paginate(100);
        return view('admin.sales.topup', compact('trx','title'));
    }
}
