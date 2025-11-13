<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RateLimiter;
use App\Http\Controllers\Process\BillProcess;
use App\Models\{
    Betsite,
    BetTrx,
    BillProduct,
    Bulksms,
    CablePlan,
    Country,
    DataBundle,
    Decoder, DatacardPlan, DataPin,
    DecoderTrx,
    Education,
    EduTrx,
    Electricity,
    Giftcard,
    Network,
    NetworkTrx,
    Operator,
    PowerTrx,
    RechargePin,
    SochiTrx,
    Transaction
};
use App\Utility\GladApi;
use App\Utility\N3tdataUtility;
use App\Utility\{
    ApiUtility,
    FlutterUtility,
    OpayUtility,
    SochiUtility
};
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BillController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(RateLimiter::class)->only([
            'buy_dataplan',
            'buy_datacard',
            'buy_airtime',
            'buy_cabletv',
            'buy_electricity',
            'buy_education',
            'send_bulksms',
            'generate_recharge_pins',
        ]);

    }
    public function bills()
    {
        return view('bills.index');
    }

    public function data()
    {
        if(sys_setting('is_data') != 1){
            return redirect()->back()->withError('Data service is currently disabled. Please try again');
        }
        $networks = Network::whereStatus(1)->whereData(1)->get();
        return view('bills.data', compact('networks'));
    }
    public function data_plan($slug)
    {
        $network = Network::whereName($slug)->first();
        if ($network == null){
            abort(404)->withError('Invalid Request. Please Try again');
        }
        $plans = DataBundle::whereNetworkId($network->id)->whereStatus(1)->whereDeleted(0)->get();

        return view('bills.dataplan', compact('plans','network'));
    }
    public function buy_dataplan(Request $request)
    {
        // validate requests
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone' => 'required|digits:11|numeric',
            'plan' => 'required|exists:data_bundles,id',
            'pin' => 'required|numeric|digits:4'
        ]);
        if ($request->amount < 0){
            return response()->json([
                'status' => 'error',
                'msg' => 'Amount must not be less than 1. Try again'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        // return $request;
        $user = Auth::user();
        if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        $plan = DataBundle::findOrFail($request->plan);
        if($user->type == 'reseller'){
            $plan->price = $plan->reseller;
        }
        $network = $plan->network;
        if ($user->balance < $plan->price){
            $response['status'] = "error";
            $response['msg'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
            return $response;
            return back()->with('error', 'You dont have enough funds in your wallet to complete this transaction');
        }
        //create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- deit, 3-others
        $trans->code = getTrans('DATA');
        $trans->message = 'Pending Purchase of '.$plan->name.' to '. $request['phone'];
        $trans->amount = $plan->price;
        $trans->status = 3;
        $trans->charge = 0;
        $trans->service = 2; // bills
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $plan->price;
        $trans->save();
        // Create network Trx
        $trx = new NetworkTrx();
        $trx->user_id = $user->id;
        $trx->network_id = $network->id;
        $trx->type = 2; // 1- airtime, 2- data, 3-swap
        $trx->code = $trans->code;
        $trx->name = $trans->message;
        $trx->amount = $request->amount;
        $trx->status = 2; //1 - success , 2- pendig, 3 -declined
        $trx->charge = 0;
        $trx->number = $request->phone;
        $trx->new_balance = $user->balance - $plan->price;
        $trx->old_balance = $user->balance;
        $trx->save();
        // deduct balance
        $user->balance = $user->balance - $plan->price;
        $user->save();
        // api transaction
        $data = [
            'amount' => $plan->price,
            'phone' => $request['phone'],
            'service' => $plan['service'],
            'network' => $network['id'],
            'plan' => $plan->id,
            'ref' => $trx->code,
        ];
        $process = new BillProcess();
        $apires = $process->purchase_data($data);
        if(isset($apires['api_status']) && $apires['api_status'] == "success"){
            // $trans->message = 'You have successfully purchased '.$plan->name.' to '. $request['phone'];
            $trans->message =  $apires['message'];
            $trans->status = 1;
            $trans->response = json_encode($apires['response']);
            $trans->save();
            // Create network Trx
            $trx->api_name = $apires['name'];
            $trx->name = $trans['message'];
            $trx->status = 1; //1 - success , 2- pendig, 3 -declined
            $trx->response = json_encode($apires['response']);
            $trx->save();
            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }
            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $plan->price);
            }
            $messg = $apires['message'];
            $res['status'] = "success";
            $res['msg'] = $messg;
            return $res;
            return redirect()->route('user.dashboard')->withSuccess('You have successfully purchased '.$plan->name.' to '. $request['phone']);

        }else{
            $trans->status = 3;
            $trans->response = json_encode($apires['response']);
            $trans->new_balance = $trans->old_balance;
            $trans->save();
            // Create network Trx
            $trx->api_name = $apires['name'];
            $trx->status = 3; //1 - success , 2- pendig, 3 -declined
            $trx->response = json_encode($apires['response']);
            $trx->new_balance = $trx->old_balance;
            $trx->save();
            // refund user
            $user->balance = $user->balance + $plan->price;
            $user->save();
            // cancel transaction

            $res['status'] = "error";
            $res['msg'] = "Transaction was not successful. Please try again";
            return $res;
            // return back()->with('error', 'Transaction wasnot successful. Please try again');
        }
        $res['status'] = "error";
        $res['msg'] = "Something went wrong. Please try again";
        return $res;
        // return back()->with('error', 'Something went wrong. Please try again');
    }
    // airtime
    public function airtime()
    {
        if(sys_setting('is_airtime') != 1){
            return redirect()->back()->withError('Airtime service is currently disabled. Please try again');
        }
        $networks = Network::whereStatus(1)->whereAirtime(1)->get();
        return view('bills.airtime', compact('networks'));
    }
    public function buy_airtime(Request $request)
    {
        // return $request;
        // validate requests
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'phone' => 'required|digits:11|numeric',
            'network' => 'required|exists:networks,id',
            'pin' => 'required|digits:4|numeric'
        ]);
        if ($request->amount < 0){
            return response()->json([
                'status' => 'error',
                'msg' => 'Amount must not be less than 1. Try again'
            ]);
            // return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        $user = Auth::user();
        if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        $network = Network::findOrFail($request->network);
        $cost = $request->amount *($network->discount /100);
        if($user->type == 'reseller'){
            $cost = $request->amount *($network->reseller /100);
        }
        if ($user->balance < $cost){
            $response['status'] = "error";
            $response['msg'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
            return $response;
            return back()->with('error', 'You dont have enough funds in your wallet to complete this transaction');
        }
        // return $request;
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- deit, 3-others
        $trans->code = getTrans('AIRTIME');
        $trans->message = "Pending Purchase of  {$network->name} Airtime worth ".format_price($request->amount) .' for '.$request->phone;
        $trans->amount = $cost;
        $trans->status = 2;
        $trans->charge = 0;
        $trans->service = 1;
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $cost;
        $trans->save();
        // Create network Trx
        $trx = new NetworkTrx();
        $trx->user_id = $user->id;
        $trx->network_id = $network->id;
        $trx->type = 1; // 1- airtime, 2- data, 3-swap
        $trx->code = $trans->code;
        $trx->name = "Pending Purchase of {$network->name} Airtime worth  ".format_price($request->amount) .' for '.$request->phone;
        $trx->amount = $cost;
        $trx->status = 2; //1 - success , 2- pending, 3 -declined
        $trx->charge = 0;
        $trx->number = $request->phone;
        $trx->new_balance = $user->balance - $cost;
        $trx->old_balance = $user->balance;
        $trx->save();
        // deduct balance
        $user->balance = $user->balance - $cost;
        $user->save();
        // api transaction
        $data = [
            'amount' => $request->amount,
            'phone' => $request['phone'],
            'network' => $network['id'],
            'ref' => $trx->code,
        ];
        $process = new BillProcess();
        $apires = $process->purchase_airtime($data);
        if(isset($apires['api_status']) && $apires['api_status'] == "success"){
            $trans->message = "You Successfully Purchased {$network->name} Airtime worth  ".format_price($request->amount) .' for '.$request->phone;
            $trans->status = 1;
            $trans->response = json_encode($apires['response']);
            $trans->save();
            // Create network Trx
            $trx->name = "You Successfully Purchased {$network->name} Airtime worth  ".format_price($request->amount) .' for '.$request->phone;
            $trx->response = json_encode($apires['response']);
            $trx->status = 1; //1 - success , 2- pendig, 3 -declined
            $trx->api_name = $apires['name'];
            $trx->save();
            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }
            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $request->amount);
            }

            $res['status'] = "success";
            $res['msg'] = "Airtime Purchase of {$network->name} ₦$request->amount to {$request->phone} was successful";
            return $res;
            // return redirect()->route('user.dashboard')->with('success', 'Airtime purchase successful');

        }else{
            $trx->new_balance = $trx->old_balance;
            $trans->new_balance = $trx->old_balance;
            $trans->status = 3;
            $trans->response = json_encode($apires);
            $trans->save();
            // Create network Trx
            $trx->api_name = $apires['name'];
            $trx->response = json_encode($apires);
            $trx->status = 3; //1 - success , 2- pendig, 3 -declined
            $trx->save();
            // refund user
            $user->balance = $user->balance + $cost;
            $user->save();
            $messg = "Please try again";
            // cancel transaction
            $res['status'] = "error";
            $res['msg'] = "Airtime Purchase of {$network->name} ₦$request->amount to {$request->phone} was not successful";
            return $res;
            // return back()->with('error', 'Transaction wasnot successful. '.$messg);
        }
        $res['status'] = "error";
        $res['msg'] = "Something went wrong. Please try again";
        return $res;
        return back()->with('error', 'Something went wrong. Please try again');
    }
    // cable TV
    public function cabletv()
    {
        if(sys_setting('is_cable') != 1){
            return redirect()->back()->withError('Cable service is currently disabled. Please try again');
        }
        $decoders = Decoder::whereStatus(1)->get();
        return view('bills.cable', compact('decoders'));
    }
    function cabletv_packages($slug){
        $decoder = Decoder::whereName($slug)->first();
        if ($decoder == null){
            abort(404)->withError('Invalid Request. Please Try again');
        }
        $plans = CablePlan::whereDecoderId($decoder->id)->whereStatus(1)->whereDeleted(0)->get();

        return view('bills.cable_buy', compact('plans','decoder'));
    }
    function cabletv_validation(Request $request)
    {
        $data = [
            'cablename' => $request->cable,
            'smart_card_number' => $request['iuc'],
        ];
        $api = new ApiUtility();
        try{
            $response = $api->validateCable($data);
            if(isset($response['name'])){
                return [
                    'status' => 'success',
                    'name' => $response['name'],
                ];
            }else{
                return [
                    'status' => 'fail',
                    'msg' => "Unable to get Customer Name. Please check and try again",
                ];
            }
        }catch(\Exception $e){
            return [
                'status' => 'fail',
                'msg' => "Unable to get Customer Name. Please check and try again",
            ];
        }

    }
    public function buy_cabletv(Request $request)
    {
        // return $request;
        // validate requests
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'number' => 'required|numeric',
            'package' => 'required|numeric',
            'pin' => 'required|numeric|digits:4'
        ]);
        if ($request->amount < 0){
            return response()->json([
                'status' => 'error',
                'msg' => 'Amount must not be less than #1. Try again'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        $user = Auth::user();
         if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        $plan = CablePlan::findOrFail($request->package);
        $decoder = $plan->decoder;
        $cost = $plan->price *(sys_setting('cable_discount') /100);
        if($user->type == 'reseller'){
            $cost = $plan->reseller *(sys_setting('cable_discount') /100);
        }
        if ($user->balance < $cost){
             $response['status'] = "error";
            $response['msg'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
            return $response;
            // return back()->with('error', 'You dont have enough funds in your wallet to complete this transaction');
        }
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- deit, 3-others
        $trans->code = getTrans('CABLE');
        $trans->message = "Pending purchase of  {$decoder->name} {$plan->name}  to {$request->number}";
        $trans->amount = $cost;
        $trans->status = 2;
        $trans->charge = 0;
        $trans->service = 5; // cable
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $cost;
        $trans->save();
        // deduct balance
        $user->balance = $user->balance - $cost;
        $user->save();
         // api transaction
        $data = [
            'amount' => $cost,
            'phone' => $user->phone,
            'name' => $request['customer_name'] ?? "Bypass User",
            'customer' => $request['number'],
            'plan' => $plan->id,
            'decoder' => $plan->decoder['id'],
            'ref' => $trans->code,
        ];
        $process = new BillProcess();
        $apires = $process->purchase_cabletv($data);

        // if( (isset($response) || isset($response['Status']) || isset($response['status'])) && ($response['Status'] == 'successful' || $response['status'] == 'successful' || $response['Status'] == 'success' || $response['status'] == 'success' ) ){
        if(isset($apires['api_status']) && $apires['api_status'] == "success"){
            $trans->message = "successfully purchase {$decoder->name} {$plan->name}  to {$request->number}";
            $trans->status = 1;
            $trans->response = json_encode($apires['response']);
            $trans->save();
            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }
            // Create network Trx
            $trx = new DecoderTrx();
            $trx->response = json_encode($apires['response']);
            $trx->user_id = $user->id;
            $trx->decoder_id = $decoder->id;
            $trx->code = $trans->code;
            $trx->name = "successfully purchase {$decoder->name} - {$plan->name}  to {$request->number}";
            $trx->message = "successfully purchase {$decoder->name} - {$plan->name}  to {$request->number}";
            $trx->amount = $cost;
            $trx->status = 1; //1 - success , 2- pending, 3 -declined
            $trx->charge = 0;
            $trx->customer_name = $request->customer_name ?? "Bypass user";
            $trx->number = $request->number;
            $trx->old_balance = $user->balance - $cost;
            $trx->new_balance = $user->balance;
            $trx->save();
            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $cost);
            }

            $res['status'] = "success";
            $res['msg'] = $trans->message;
            return $res;
            return redirect()->route('user.dashboard')->withSuccess($plan->name.' purchase successful');

        }else{
            $trans->new_balance = $user->balance + $cost;
            $trans->message = "Transaction failed for {$decoder->name} - {$plan->name}  to {$request->number}";
            $trans->status = 3;
            $trans->response = json_encode($apires['response']);
            $trans->save();
            // refund user
            $user->balance = $user->balance + $cost;
            $user->save();
            $res['status'] = "error";
            $res['msg'] = $trans->message;
            return $res;
            // cancel transaction
            return back()->with('error', 'Transaction was not successful. Please try again');
        }
        $res['status'] = "error";
        $res['msg'] = "Something went wrong. Please try again";
        return $res;
        return back()->with('error', 'Something went wrong. Please try again');
    }

    public function bulksms()
    {
        if(sys_setting('is_bulksms') != 1){
            return redirect()->back()->withError('Bulksms service is currently disabled. Please try again');
        }
        return view('bills.bulksms');
    }
    public function send_bulksms(Request $request){
        // return $request->all();
        $request->validate([
            'message' => 'required|string',
            'sender' => 'required|string',
            'number' => 'required|string',
            'pin' => 'required|numeric|digits:4',
            'amount' => 'required|string',
        ]);
        $user = Auth::user();
        if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        $phones = (explode(' ', $request->number));
        $phone_count = count($phones);
        $result = strlen($request['message']);
        $results = ceil($result / 152);
        $count_message = $phone_count * $results;
        if($phone_count >= '10000'){
            $response['status'] = "error";
            $response['message'] = "Maximum Number You Are allowed to send a message to is 10,000";
            return $response;
        }
        if(strlen($request->sender) > 10){
            $response['status'] = "error";
            $response['message'] = "Sender Name Maximum is 10 characters";
            return $response;
        }
        if($result >= 4000){
            $response['status'] = "error";
            $response['message'] = "Message must be 4000 characters";
            return $response;
        }
        $smsfee = sys_setting('bulksms_price');
        if($user->type == 'reseller'){
            $smsfee = sys_setting('reseller_bulksms_price');
        }
        $final_amount = $smsfee * $count_message;
        $reference = getTrans("BULKSMS");
        $real_number = [];
        $wrong_number = [];

        for($a=0;$a<($phone_count);$a++){
            $check_number = $phones[$a];
            if((substr($check_number,0,1) == '0' xor substr($check_number,0,3) == '234')){
                $check_adex = strlen($check_number);
                if($check_adex == '11' xor $check_adex == '13'){
                  if(is_numeric($check_number)){
                    $real_number[] = $check_number;
                  }else{
                    $wrong_number[] = $check_number;
                  }
                }else{
                  $wrong_number[] = $check_number;
                }
            }else{
                $wrong_number[] = $check_number;
            }
        }
        if( $real_number != null){
            if ($user->balance < $final_amount){
                $response['status'] = "error";
               $response['message'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
               return $response;
           }
           if(!empty($wrong_number)){
               $total_wrong_number = count($wrong_number);
               $wrong= implode(",",$wrong_number);
           }else{
               $total_wrong_number = "0";
               $wrong = "0";
           }
           $num = implode(",",$phones);
            $total_real_number = count($real_number);
            $real = implode(",",$real_number);

            // deduct user
            $user->balance = $user->balance - $final_amount;
            $user->save();
            $trx = new Bulksms();
            $trx->user_id = $user->id;
            $trx->amount = $final_amount;
            $trx->code = $reference;
            $trx->message = $request->message;
            $trx->total_number = $phone_count;
            $trx->total_wrong_number = $total_wrong_number;
            $trx->total_real_number = $total_real_number;
            $trx->sender = $request->sender;
            $trx->wrong_number = $wrong;
            $trx->real_number = $real;
            $trx->number = $num;
            $trx->status = 2; //1 - success , 2- pending, 3 -declined, 4-refund
            $trx->old_balance = $user->balance + $final_amount;
            $trx->new_balance = $user->balance;
            $trx->save();
            // create message
            $trans = new Transaction();
            $trans->user_id = $user->id;
            $trans->type = 2; // 1- credit, 2- deit, 3-others
            $trans->code = $reference;
            $trans->message = "Bulk SMS Transaction on Process";
            $trans->amount = $final_amount;
            $trans->status = 2;
            $trans->charge = 0;
            $trans->service = 8; //
            $trans->old_balance = $user->balance + $final_amount;
            $trans->new_balance = $user->balance;
            $trans->save();

            $data = [
                'sender' => $request->sender,
                'number' => $real_number,
                'ref' => $reference,
                'message' => $request->message,
                'real' => $real,
            ];
            $process = new BillProcess();
            $apires = $process->send_bulksms($data);
            if(isset($apires['api_status']) && $apires['api_status'] == "success"){
                $trans->status = 1;
                $trans->message = "BULK SMS TRANSACTION SUCCESSFUL";
                $trans->response = $apires;
                $trans->save();
                $trx->status = 1;
                $trx->response = $apires['response'];
                $trx->api_reference = $apires['ref'];
                $trx->api_name = $apires['name'];
                $trx->save();

                if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                    \send_emails($user->email, 'TRX_EMAIL',
                    [
                        'username' => $user['username'],
                        'code' => $trans->code,
                        'trx_details' => $trans->message,
                        'trx_type' => trans_type2($trans->type),
                        'amount' => format_price($trans['amount']),
                        'date' => $trans->created_at
                    ]);
                }

                if(sys_setting('is_affiliate') == 1){
                    give_affiliate_bonus($user->id, $final_amount);
                }
                $res['status'] = "success";
                $res['msg'] = $trans->message;
                return $res;
            } else{
                $user->balance = $user->balance + $final_amount;
                $user->save();
                $trans->new_balance = $user->balance;
                $trans->status = 3;
                $trans->response = $apires;
                $trans->message = "Transaction fail for BULK SMS ";
                $trans->save();
                $trx->status = 3;
                $trx->new_balance = $user->balance;
                $trx->response = $apires['response'];
                $trx->api_reference = $apires['ref'];
                $trx->api_name = $apires['name'];
                $trx->save();

                $response['status'] = "error";
                $response['msg'] = $trans['message'];
                return $response;
            }
        }else{
            $total_wrong_number = count($wrong_number);
            $wrong= implode(",",$wrong_number);
            $num = implode(",",$phones);

            // bulk sms
            $trx = new Bulksms();
            $trx->user_id = $user->id;
            $trx->amount = $final_amount;
            $trx->code = $reference;
            $trx->message = $request->message;
            $trx->total_number = $phone_count;
            $trx->total_wrong_number = $total_wrong_number;
            $trx->total_real_number = 0;
            $trx->sender = $request->sender;
            $trx->wrong_number = $wrong;
            $trx->real_number = 0;
            $trx->number = $num;
            $trx->status = 3; //1 - success , 2- pending, 3 -declined, 4-refund
            $trx->old_balance = $user->balance;
            $trx->new_balance = $user->balance;
            $trx->save();
            // create message
            $trans = new Transaction();
            $trans->user_id = $user->id;
            $trans->type = 2; // 1- credit, 2- deit, 3-others
            $trans->code = $reference;
            $trans->message = "Bulk SMS Not Sent. Please Check Numbers and Try again";
            $trans->amount = $final_amount;
            $trans->status = 3;
            $trans->charge = 0;
            $trans->service = 8; //
            $trans->old_balance = $user->balance;
            $trans->new_balance = $user->balance;
            $trans->save();

            $response['status'] = "error";
            $response['amount'] = "₦".number_format($final_amount,2);
            $response['total_number'] = $phone_count;
            $response['wrong_number'] = $wrong;
            $response['correct_number'] = "0";
            $response['total_wrong_number'] = $total_wrong_number;
            $response['number'] = $num;
            $response['total_correct_number'] = "0";
            $response['msg'] = $trans->message;
            return $response;
        }
        return [
            'status' => 'error',
            'msg' => 'Something went wrong. Please try again'
        ];
    }

    public function education()
    {
        if(sys_setting('is_education') != 1){
            return redirect()->back()->withError('Exam PIN service is currently disabled. Please try again');
        }
        $education = Education::whereStatus(1)->get();
        return view('bills.education' ,compact('education'));
    }
    public function buy_education(Request $request){
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'quantity' => 'required|numeric|min:1',
            'service' => 'required|numeric|exists:education,id',
            'pin' => 'required|numeric|digits:4'
        ]);
        if ($request->quantity < 0){
            return response()->json([
                'status' => 'error',
                'msg' => 'Quantity must not be less than 1. Try again'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        if ($request->quantity >5){
            return response()->json([
                'status' => 'error',
                'msg' => 'Quantity must not be More than 5. Please Try again'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        if ($request->amount < 0){
            return response()->json([
                'status' => 'error',
                'msg' => 'Amount must not be less than 1. Try again'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        $user = Auth::user();
        if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        $plan = Education::findOrFail($request->service);
        $cost = $request->quantity * $plan->price;
        if($user->type == 'reseller'){
            $plan->price = $plan->reseller;
            $cost = $request->quantity * $plan->reseller;
        }
        if ($user->balance < $cost){
            $response['status'] = "error";
            $response['msg'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
            return $response;
            return back()->with('error', 'You dont have enough funds in your wallet to complete this transaction');
        }
        // deduct balance
        $user->balance = $user->balance - $cost;
        $user->save();

        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- deit, 3-others
        $trans->code = getTrans('EXAM');
        $trans->message = $plan->name ." pin ₦{$cost} Pending Transactions";
        $trans->amount = $cost;
        $trans->status = 2;
        $trans->charge = 0;
        $trans->service = 6; // education
        $trans->new_balance = $user->balance;
        $trans->old_balance = $user->balance + $cost;
        $trans->save();
        // Api trans
        $data = [
            'name' => strtoupper($plan['code']),
            'exam' => $plan->id,
            'quantity' => $request['quantity'],
            'ref' => $trans->code,
        ];
        $process = new BillProcess();
        $response = $process->purchase_exam($data);

        // $api = new ApiUtility();
        // $response = $api->buyExamPins($data);
        // $response= 1;
        if(isset($response['api_status']) && $response['api_status']== "success"){
            // create transaction
            $trans->status = 1;
            $trans->message = $plan->name ." pin ₦{$cost} Purchased successfully.";
            $trans->response = json_encode($response);
            $trans->save();
            // Create Education Trx
            $trx = new EduTrx();
            $trx->user_id = $user->id;
            $trx->education_id = $plan->id;
            $trx->code = $trans->code;
            $trx->quantity = $request->quantity;
            $trx->name = $plan->name ." pin ₦{$cost} Purchase successful";
            $trx->amount = $cost;
            $trx->status = 1; //1 - success , 2- pending, 3 -declined
            $trx->charge = 0;
            $trx->response = json_encode($response);
            $trx->pins = $response['pin'];
            $trx->serial = $response['serial'] ?? " ";
            $trx->old_balance = $user->balance + $cost;
            $trx->new_balance = $user->balance;
            $trx->save();
            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }
            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $cost);
            }
            $res['status'] = "success";
            $res['msg'] = $trans['message'];
            return $res;
            return redirect()->route('user.dashboard')->withSuccess($plan->name.' purchase successful');

        }else{
            // refund user
            $trans->response = json_encode($response);
            $trans->status = 3;
            $trans->new_balance = $trans->old_balance;
            $trans->save();
            $user->balance = $user->balance + $cost;
            $user->save();
            // cancel transaction
            $res['status'] = "error";
            $res['msg'] = "Transaction was not successful. Please try again";
            return $res;
            return back()->with('error', 'Transaction was not successful. Please try again');
        }
        $res['status'] = "error";
        $res['msg'] = "Something went wrong and Transaction not successful. Please try again";
        return $res;
        return back()->with('error', 'Something went wrong. Please try again');
    }
    public function datacard()
    {
        if(sys_setting('is_datacard') != 1){
            return redirect()->back()->withError('Datacard service is currently disabled. Please try again');
        }
        $networks = Network::whereStatus(1)->whereDatacard(1)->get();
        return view('bills.datacard', compact('networks'));
    }
    public function datacard_plan($slug)
    {
        $network = Network::whereName($slug)->first();
        if ($network == null){
            abort(404)->withError('Invalid Request. Please Try again');
        }
        $plans = DatacardPlan::whereNetworkId($network->id)->whereStatus(1)->whereDeleted(0)->get();

        return view('bills.datacardplan', compact('plans','network'));
    }
    public function buy_datacard(Request $request){
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'quantity' => 'required|numeric|min:1',
            'plan' => 'required|numeric',
            'pin' => 'required|digits:4|numeric',
            'name' => 'string|required'
        ]);
        if ($request->quantity < 0){
            return response()->json([
                'status' => 'error',
                'msg' => 'Quantity must not be less than 1. Try again'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        if ($request->quantity > 50){
            return response()->json([
                'status' => 'error',
                'msg' => 'Quantity can not be more than 50'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        if ($request->amount < 0){
            return response()->json([
                'status' => 'error',
                'msg' => 'Amount must not be less than 1. Try again'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        $user = Auth::user();
        if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        $plan = DatacardPlan::findOrFail($request->plan);
        $cost = $plan->price * $request->quantity;
        if($user->type == 'reseller'){
            $plan->price = $plan->reseller;
            $cost = $plan->reseller * $request->quantity;
        }
        $network = $plan->network;
        if ($user->balance < $cost){
            $response['status'] = "error";
            $response['msg'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
            return $response;
            return back()->with('error', 'You dont have enough funds in your wallet to complete this transaction');
        }
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- deit, 3-others
        $trans->code = getTrans('DATACARD');
        $trans->message = "{$plan->name} Datcard Pending Transaction";
        $trans->amount = $cost;
        $trans->status = 3;
        $trans->charge = 0;
        $trans->service = 11;
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $cost;
        $trans->save();
        // save pins to database
        $voucher = new DataPin();
        $voucher->plan_id = $plan->id;
        $voucher->user_id = $user->id;
        $voucher->network_id = $network->id; // 1- credit, 2- deit, 3-others
        $voucher->code = $trans['code'];
        $voucher->message = $trans->message;
        $voucher->amount = $cost;
        $voucher->name = $request->name;
        $voucher->cost = $plan->price;
        $voucher->status = 1; //1 - success , 2- pendig, 3 -declined
        $voucher->charge = 0;
        $voucher->quantity = $request->quantity;
        $voucher->old_balance = $user->balance ;
        $voucher->new_balance = $user->balance - $cost;
        $voucher->save();

        // deduct user balance
        $user->balance = $user->balance - $cost;
        $user->save();
        // api transaction
        $data = [
            'network' => $network->id,
            'plan_type' => $plan->n3tdata,
            'plan_id' => $plan->id,
            'quantity' => $request->quantity,
            'name'  => $request->name,
            'ref' => $trans->code,
        ];
        $process = new BillProcess();
        $response = $process->purchase_datacard($data);
        if(isset($response['api_status']) && $response['api_status']== "success"){
            // create transaction
            $trans->status = 1;
            $trans->message = "{$plan->name} Datcard Generated Successfully";
            $trans->response = json_encode($response);
            $trans->save();
            // save pins to database
            $voucher->message = $trans->message;
            $voucher->check_bal  = $response["check_balance"];
            $voucher->load_code = $response["load_pin"];
            $voucher->serial = json_encode($response['serial']);
            $voucher->pins = json_encode($response['pin']);
            $voucher->status = 1; //1 - success , 2- pendig, 3 -declined
            $voucher->response = json_encode($response);
            $voucher->save();
            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }
            $res['status'] = "success";
            $res['msg'] = "Data Pins generated successfully ";
            $res['url'] = route('user.datacard.view', $voucher->id);
            return $res;

            return redirect()->route('user.datacard.view', $voucher->id)->with('success', 'DataPins generated Successfully');
            // return back()->with('success', 'Recharge Pins generated Successfully');

        }else{
            // refund user
            $user->balance = $user->balance + $cost;
            $user->save();
            // cancel transaction
            $trans->message = "{$plan->name} Datcard Failed Transaction";
            $trans->response = json_encode($response);
            $trans->status = 3;
            $trans->new_balance = $user->balance;
            $trans->save();
            $voucher->status = 3;
            $voucher->message = $trans->message;
            $voucher->new_balance = $trans->old_balance;
            $voucher->response = json_encode($response);
            $voucher->save();

            $res['status'] = "error";
            $res['msg'] = "Transaction was not successful. Please try again";
            return $res;
            return back()->with('error', 'Transaction was not successful. Please try again');
        }

        $res['status'] = "error";
        $res['msg'] = "Transaction was not successful. Something went wrong Please try again";
        return $res;
        return back()->with('error', 'Something went wrong. Please try again');
    }

    public function airtime_cash()
    {
        if(sys_setting('airtime_cash') != 1){
            return redirect()->back()->withError('Airtime Swap service is currently disabled. Please try again');
        }
        $networks = Network::whereStatus(1)->whereSwap(1)->get();
        return view('bills.a2c', compact('networks'));
    }
    public function airtime_swap(Request $request){
        $request->validate([
            'rate' => 'required|numeric',
            'amount' => 'required|numeric|min:1000',
            'phone' => 'required|numeric|digits:11',
            'network' => 'required|numeric|exists:networks,id'
        ]);
        if ($request->amount < 0){
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        $user = Auth::user();
        // Save to database
        $network = Network::findOrFail($request->network);
        $trans = new NetworkTrx();
        $trans->user_id = $user->id;
        $trans->network_id = $network->id;
        $trans->type = 3; // 1- airtime, 2- data, 3-swap
        $trans->code = getTrans('A2C');
        $trans->name = "Airtime Swap of ".sym_price($request->amount);
        $trans->amount = $request->amount;
        $trans->status = 2; //1 - success , 2- pendig, 3 -declined
        $trans->charge = $request->amount * $network->rate/100;
        $trans->number = $request->phone;
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance;
        $trans->save();
        $message = 'Your '.$network->name.' Airtime conversion from phone number '.$request->phone.' has been successfully sent to the admin for confirmation.
        The unit price of your airtime will be credited to your deposit balance once we verify your conversion';
        return redirect()->route('user.swap.logs')->with('success', $message);
    }

    public function electricity()
    {
        if(sys_setting('is_electricity') != 1){
            return redirect()->back()->withError('Electricity service is currently disabled. Please try again');
        }
        $powers = Electricity::whereStatus(1)->whereDeleted(0)->get();
        return view('bills.electricity', compact('powers'));
    }
    function electricity_validation(Request $request)
    {
        $disco = Electricity::findOrFail($request->disco);
        // use sochi
        $slot = new SochiUtility();
        $comm = "execTransaction";
        $payload = [
            "accountId" => $request['meter'],
            'productId' => $disco->sochi_product,
        ];
        try{
            $response = $slot->makeRequest($comm, $payload);
            if(isset($api_result['status']) && $api_result['status']['typeName']== "Success"){
                return [
                    'status' => 'success',
                    'name' => $response['result']['customerName'] ?? "",
                    'msg' => " Customer Name Validated.",
                ];
            }else{
                return [
                    'status' => 'fail',
                    'msg' => "Unable to Validate Customer Name. Please check and try again",
                ];
            }
        }catch(\Exception $e){
            return [
                'status' => 'fail',
                'msg' => "Unable to get Customer Name. Please check and try again",
            ];
        }

    }
    public function buy_electricity(Request $request){
        // return $request;
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'number' => 'required|numeric',
            'meter' => 'required|numeric',
            'disco' => 'required|numeric|exists:electricities,id',
            'pin' => 'required|numeric|digits:4',
            'customer_name' => 'required|string'
        ]);
        if ($request->amount < 0){
            return response()->json([
                'status' => 'error',
                'msg' => 'Amount must not be less than #1. Try again'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        $user = Auth::user();
        if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        $disco = Electricity::findOrFail($request->disco);
        $cost = $request->amount + $disco->fee;
        if ($disco->minimum > $request->amount){
            return response()->json([
                'status' => 'error',
                'msg' => 'Amount is lower than minimum price '.format_price($disco->minimum),
            ]);
            return back()->with('error', 'Amount is lower than minimum price '.format_price($disco->minimum));
        }
        if ($user->balance < $cost){
            return response()->json([
                'status' => 'error',
                'msg' => "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance),
            ]);
            return back()->with('error', 'You dont have enough funds in your wallet to complete this transaction');
        }
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- deit, 3-others
        $trans->code = getTrans('POWER');
        $trans->message ='Pending transaction '.$request['amount'].' Purchase '. $disco->name.' for '.$request->number;
        $trans->amount = $request->amount;
        $trans->status = 3;
        $trans->charge = $disco->fee;
        $trans->service = 7; // electricity
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $request->amount;
        $trans->save();
        $trx = new PowerTrx();
        $trx->user_id = $user->id;
        $trx->electricity_id = $disco->id;
        $trx->code = $trans['code'];
        $trx->name = "Pending purchase of {$disco->name} - {$request->amount} to {$request->number}";
        $trx->amount = $request->amount;
        $trx->status = 2; //1 - success , 2- pending, 3 -declined
        $trx->charge = $disco->fee;
        $trx->customer_name = $request->customer_name;
        $trx->number = $request->number;
        $trx->old_balance = $user->balance;
        $trx->new_balance = $user->balance - $cost;
        $trx->save();

        // deduct balance
        $user->balance = $user->balance - $request->amount;
        $user->save();
         // api transaction

        $data = [
            'name' => $request->customer_name,
            'type' => $request->meter,
            'amount' => $request->amount,
            'service' => $disco['code'],
            'number' => $request->number,
            'phone' => $user->phone ?? "",
            'disco' => $disco['id'],
            'ref' => $trans->code,
        ];

        $process = new BillProcess();
        $response = $process->purchase_power($data);
        // $api = new ApiUtility();
        // $response = $api->buyPower($data);

        if(isset($response['api_status']) && $response['api_status'] == "success"){
            $token = $response['token'] ?? " ";
            $trans->status = 1;
            $trans->message = "successfully purchase {$disco->name} {$request->amount} to {$request->number}.-Token {$token}";
            $trans->response = json_encode($response);
            $trans->save();
            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }
            // Create network Trx
            $trx->name = "successfully purchase {$disco->name} - {$request->amount} to {$request->number}";
            $trx->status = 1; //1 - success , 2- pending, 3 -declined
            $trx->token = $token ?? " ";
            $trx->response = json_encode($response);
            $trx->save();

            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $request->amount);
            }
            $res['status'] = "success";
            $res['msg'] = "Successfully purchase {$disco->name} - {$request->amount} to {$request->number} Token - {$token}";
            return $res;
            return redirect()->route('user.dashboard')->withSuccess($disco->name.' purchase successful');
        }else{
            $trans->status = 3;
            $trans->new_balance = $trans->old_balance;
            $trans->response = json_encode($response);
            $trans->message = "Failed purchase of {$disco->name} - {$request->amount} to {$request->number}";
            $trans->save();
            $trx->status = 3;
            $trx->name = "Failed purchase of {$disco->name} - {$request->amount} to {$request->number}";
            $trx->new_balance = $trans->old_balance;
            $trx->response = json_encode($response);
            $trx->save();

            // refund user
            $user->balance = $user->balance + $request->amount;
            $user->save();
            // cancel transaction
            $res['status'] = "error";
            $res['msg'] = "Transaction was not successful. Please try again";
            return $res;
            return back()->with('error', 'Transaction was not successful. Please try again');
        }
        $res['status'] = "error";
        $res['msg'] = "Something went wrong. Please try again";
        return $res;
        return back()->with('error', 'Something went wrong. Please try again');
    }

    public function recharge_pins()
    {
        if(sys_setting('airtime_pin') != 1){
            return redirect()->back()->withError('Airtime PIN service is currently disabled. Please try again');
        }
        $networks = Network::whereStatus(1)->whereCardpin(1)->get();
        return view('bills.airtimepin', compact('networks'));
    }

    public function generate_recharge_pins(Request $request){
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'quantity' => 'required|numeric|min:1',
            'network' => 'required|exists:networks,id',
            'value' => 'required|numeric|min:1',
            'pin' => 'required|digits:4|numeric',
            'name' => 'string|required'
        ]);
        if ($request->quantity < 0){
            return response()->json([
                'status' => 'error',
                'msg' => 'Quantity must not be less than 1. Try again'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        if ($request->quantity > 50){
            return response()->json([
                'status' => 'error',
                'msg' => 'Quantity can not be more than 50.'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        if ($request->amount < 0){
            return response()->json([
                'status' => 'error',
                'msg' => 'Amount must not be less than 1. Try again'
            ]);
            return back()->with('error', 'Thief !. You cant complete this transaction');
        }
        $user = Auth::user();
        if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        $cost = $request->value * $request->quantity;
        $network = Network::find($request->network);
        $amount = $cost *($network->pin_discount /100);
        if($user->type == 'reseller'){
            $amount = $cost *($network->reseller_pin /100);
        }
        if ($user->balance < $amount){
            $response['status'] = "error";
            $response['msg'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
            return $response;
            return back()->with('error', 'You dont have enough funds in your wallet to complete this transaction');
        }
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- deit, 3-others
        $trans->code = getTrans('CARDPIN');
        $trans->message = "{$network->name} - {$request->value} Recharge Card PIN Pending Transactions";
        $trans->amount = $amount;
        $trans->status = 2;
        $trans->charge = 0;
        $trans->service = 4;
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $amount;
        $trans->save();
        // deduct user balance
        $user->balance = $user->balance - $amount;
        $user->save();
        // api transaction
        $data = [
            'network' => $network->code,
            'value' => rechargepin_id($network->name, $request['value']),
            'quantity' => $request->quantity,
            'name'  => $request->name
        ];
        $api = new ApiUtility();
        $response = $api->buyGeneratePins($data);
        if(isset($response['Status']) && $response['Status']== "successful"){
            // create transaction
            $trans->status = 1;
            $trans->message = "{$network->name} - {$request->value} Recharge Card PIN Generated Successfully";
            $trans->response = json_encode($response);
            $trans->save();
            // save pins to database
            $voucher = new RechargePin();
            $voucher->user_id = $user->id;
            $voucher->network_id = $network->id; // 1- credit, 2- deit, 3-others
            $voucher->code = $trans['code'];
            $voucher->message = $trans->message;
            $voucher->load_code = $response['data_pin'][0]['fields']['load_code'];
            $voucher->pins = json_encode($response['data_pin']);
            $voucher->amount = $amount;
            $voucher->name = $request->name;
            $voucher->cost = $request->amount;
            $voucher->status = 1; //1 - success , 2- pendig, 3 -declined
            $voucher->charge = 0;
            $voucher->quantity = $request->quantity;
            $voucher->old_balance = $user->balance + $amount;
            $voucher->new_balance = $user->balance;
            $voucher->response = json_encode($response);
            $voucher->save();
            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }
            $res['status'] = "success";
            $res['msg'] = "Recharge Pins generated successfully ";
            $res['url'] = route('user.voucher.view', $voucher->id);
            return $res;

            return redirect()->route('user.voucher.view', $voucher->id)->with('success', 'Recharge Pins generated Successfully');
            // return back()->with('success', 'Recharge Pins generated Successfully');

        }else{
            // refund user
            $user->balance = $user->balance + $amount;
            $user->save();
            // cancel transaction
            $trans->new_balance = $trans->old_balance;
            $trans->response = json_encode($response);
            $trans->status = 3;
            $trans->save();

            $res['status'] = "error";
            $res['msg'] = "Transaction was not successful. Please try again";
            return $res;
            return back()->with('error', 'Transaction was not successful. Please try again');
        }

        $res['status'] = "error";
        $res['msg'] = "Transaction was not successful. Something went wrong Please try again";
        return $res;
        return back()->with('error', 'Something went wrong. Please try again');
    }

    // Betting
    public function betting()
    {
        if(sys_setting('is_betting') != 1){
            return redirect()->back()->withError('Bet Payment is currently disabled. Please try again');
        }
        $plans = Betsite::whereStatus(1)->get();
        return view('bills.bet', compact('plans'));
    }
    function bet_validation(Request $request)
    {
        $betsite = Betsite::findOrFail($request->betsite);
        $data = [
            "serviceType" => "betting",
            'provider' => $betsite->code,
            'customerId' => $request['accountId'],
        ];

        $api = new OpayUtility();
        try{
            $response = $api->verifyBet($data);
            if($response['success'] == true && $response['code'] == "00000"){
                return [
                    'status' => 'success',
                    'username' => $response['userName'],
                    'name' => $response['firstName'] . $response['lastName'],
                ];
            }else{
                return [
                    'status' => 'fail',
                    'msg' => "Invalid Account ID. Please check and try again",
                ];
            }
        }catch(\Exception $e){
            return [
                'status' => 'fail',
                'msg' => "Unable to get Customer Name. Please check and try again",
            ];
        }

    }
    public function buy_betting(Request $request){
        // return $request;
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'number' => 'required|string',
            'betsite' => 'required|numeric|exists:betsites,id',
            'pin' => 'required|numeric|digits:4',
            'customer_name' => 'nullable'
        ]);
        if ($request->amount < 0){
            return response()->json([
                'status' => 'error',
                'msg' => 'Amount must not be less than #1. Try again'
            ]);
        }
        $user = Auth::user();
        if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        $disco = Betsite::findOrFail($request->betsite);
        $cost = $request->amount + $disco->fee;
        if ($disco->minimum > $request->amount){
            return response()->json([
                'status' => 'error',
                'msg' => 'Amount is lower than minimum price '.format_price($disco->minimum),
            ]);
        }
        if ($user->balance < $cost){
            return response()->json([
                'status' => 'error',
                'msg' => "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance),
            ]);
        }
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- deit, 3-others
        $trans->code = getTrans('BET');
        $trans->message ='Pending transaction '.$request['amount'].' Purchase '. $disco->name.' for '.$request->number;
        $trans->amount = $cost;
        $trans->status = 3;
        $trans->charge = $disco->fee;
        $trans->service = 12; // betting
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $cost;
        $trans->save();
        $trx = new BetTrx();
        $trx->user_id = $user->id;
        $trx->betsite_id = $disco->id;
        $trx->code = $trans['code'];
        $trx->name = "Pending purchase of {$disco->name} - {$request->amount} to {$request->number}";
        $trx->amount = $cost;
        $trx->status = 2; //1 - success , 2- pending, 3 -declined
        $trx->charge = $disco->fee;
        $trx->customer_name = $request->customer_name ?? "";
        $trx->number = $request->number;
        $trx->old_balance = $user->balance;
        $trx->new_balance = $user->balance - $cost;
        $trx->save();

        // deduct balance
        $user->balance = $user->balance - $cost;
        $user->save();
        // api transaction
        $data = [
            'amount' => $request->amount,
            'service' => $disco['code'],
            'number' => $request->number,
            'betsite' => $disco['id'],
            'ref' => $trans->code,
            'name' => $request->customer_name,
        ];
        $process = new BillProcess();
        $response = $process->purchase_bet($data);

        if(isset($response['api_status']) && $response['api_status'] == "success"){
            $trans->status = 1;
            $trans->message = "successfully purchase {$disco->name} {$request->amount} to {$request->number}";
            $trans->response = json_encode($response);
            $trans->save();
            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }
            // Create network Trx
            $trx->name = "Successfully purchase {$disco->name} - {$request->amount} to {$request->number}";
            $trx->status = 1; //1 - success , 2- pending, 3 -declined
            $trx->response = json_encode($response);
            $trx->save();

            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $request->amount);
            }
            $res['status'] = "success";
            $res['msg'] = $trx->name;
            return $res;
        }else{
            $trans->status = 3;
            $trans->new_balance = $trans->old_balance;
            $trans->response = json_encode($response);
            $trans->save();
            $trx->status = 3;
            $trx->name = "Failed purchase of {$disco->name} - {$request->amount} to {$request->number}";
            $trx->new_balance = $trans->old_balance;
            $trx->response = json_encode($response);
            $trx->save();
            // refund user
            $user->balance = $user->balance + $cost;
            $user->save();
            // cancel transaction
            $res['status'] = "error";
            $res['msg'] = "Transaction was not successful. Please try again";
            return $res;
        }
        $res['status'] = "error";
        $res['msg'] = "Something went wrong. Please try again";
        return $res;
    }


    // Other Utiilities
    public function utilityBills()
    {
        if (sys_setting('religious_pay') == 1 || sys_setting('tax_pay') == 1 || sys_setting('translog_pay') == 1) {
            return view('bills.utility');
        }
        return redirect()->back()->withErrors('Utility Payments are disabled. Please try again');
    }

    public function utilityBillers(Request $request){
        $flutter =  new FlutterUtility();
        $code = strtoupper($request->category);
        if($code == null){
            return [
                'status' => 'error',
                'message' => "Please Select Category",
            ];
        }
        try{
            $res = $flutter->getBillers($code);
            if($res['data']){
                return [
                    'status'=> 'success',
                    'message'   => 'Services fetched successfully',
                    'data'  => $res['data']
                ];
            }
            return [
                'status'=> 'error',
                'message'   => "Please try again",
                'data'  => null
            ];
        }catch(\Exception $e){
            return [
                'status' => 'error',
                'message' => "Unable to get Services. Please try again",
            ];
        }

    }
    public function utilityPlans(Request $request){
        $flutter =  new FlutterUtility();
        $code = strtoupper($request->billerCode);
        if($code == null){
            return [
                'status' => 'error',
                'message' => "Please Select Service",
            ];
        }
        try{
            $res = $flutter->getBillerItems($code);
            if($res['data']){
                return [
                    'status'=> 'success',
                    'message'   => 'Plans fetched successfully',
                    'data'  => $res['data']
                ];
            }
            return [
                'status'=> 'error',
                'message'   => "Unable to get service",
                'data'  => null
            ];
        }catch(\Exception $e){
            return [
                'status' => 'error',
                'message' => "Unable to get Plans. Please try again",
            ];
        }

    }

    function utilityPay (Request $request){
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'category' => 'required|string|in:TAX,TRANSLOG,RELINST',
            'number' => 'required|string',
            'item_code' => 'required|string',
            'biller_code' => 'required|string',
            'item_name' => 'required|string',
            'biller_name' => 'nullable|string',
            'pin' => 'required|numeric|digits:4',
        ]);
        $user = Auth::user();
        if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        $fee = 100;
        if($request->category == "TAX"){
            $fee = sys_setting('tax_fee');
        }elseif($request->category == "TRANSLOG"){
            $fee = sys_setting('translog_fee');
        }elseif($request->category == "RELINST"){
            $fee = sys_setting('religious_fee');
        }
        $cost = $request->amount + $fee;
        if ($user->balance < $cost){
            return response()->json([
                'status' => 'error',
                'msg' => "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance),
            ]);
        }

        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- debit, 3-others
        $trans->code = getTrans('UTILITY');
        $trans->message ='Payment of '.$request['number'].' to '.$request->item_name;
        $trans->amount = $cost;
        $trans->status = 3;
        $trans->charge = $fee;
        $trans->service = strtolower($request->category);
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $cost;
        $trans->meta = [
            'number' => $request->number,
            'service' => $request->item_name
        ];
        $trans->save();
        // deduct balance
        $user->balance = $user->balance - $cost;
        $user->save();
        // api transaction
        $data = [
            'amount' => $request->amount,
            'biller_code' => $request->biller_code,
            'item_code' => $request->item_code,
            'number' => $request->number,
            'ref' => $trans->code,
        ];
        // if religious, do manual
        if($request->category == "RELINST"){
            $response = ["message" => "Payment Was successful"];
            $trans->status = 1;
            $trans->message = 'Successful '.$request['number'].' payment to '.$request->item_name;
            $trans->response = json_encode($response);
            $trans->save();
            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }

            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $request->amount);
            }
            $res['status'] = "success";
            $res['msg'] = $trans->message;
            return $res;
        }else{
            $process = new BillProcess();
            $response = $process->purchase_utility($data);
            if(isset($response['api_status']) && $response['api_status'] == "success"){
                $trans->status = 1;
                $trans->message = 'Successful '.$request['number'].' payment to '.$request->item_name;
                $trans->response = json_encode($response);
                $trans->save();
                // send trxn email
                if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                    \send_emails($user->email, 'TRX_EMAIL',
                    [
                        'username' => $user['username'],
                        'code' => $trans->code,
                        'trx_details' => $trans->message,
                        'trx_type' => trans_type2($trans->type),
                        'amount' => format_price($trans['amount']),
                        'date' => $trans->created_at
                    ]);
                }

                // give referral bonus
                if(sys_setting('is_affiliate') == 1){
                    give_affiliate_bonus($user->id, $request->amount);
                }
                $res['status'] = "success";
                $res['msg'] = $trans->message;
                return $res;
            }else{
                $trans->status = 3;
                $trans->new_balance = $trans->old_balance;
                $trans->response = json_encode($response);
                $trans->message = 'Failed Payment of '.$request['item_name'].' to '.$request->number;
                $trans->save();
                // refund user
                $user->balance = $user->balance + $cost;
                $user->save();
                // cancel transaction
                $res['status'] = "error";
                $res['msg'] = "Transaction was not successful. Please try again";
                return $res;
            }
        }
        $res['status'] = "error";
        $res['msg'] = "Something went wrong. Please try again";
        return $res;
    }

    // international topup
    public function topup()
    {
        $countries  = Country::whereStatus(1)->orderBy('name')->get();
        if(sys_setting('is_topup') != 1){
            return redirect()->back()->withError('International Airtime service is currently disabled. Please try again');
        }
        return view('bills.global-airtime', compact('countries'));

        $countries  = file_get_contents(resource_path('views/countries.json'));
        $countries =  json_decode($countries, true);
        if(sys_setting('is_topup') != 1){
            return redirect()->back()->withError('Airtime service is currently disabled. Please try again');
        }
        return view('bills.topup', compact('countries'));
    }

    function validateNumber(Request $request){
        $api = new SochiUtility ();
        $command = "parseMsisdn";
        $data = [
            "msisdn" => $request->msisdn,
        ];
        $res = $api->makeRequest($command, $data);
        if(isset($res['status']) && $res['status']['typeName']== "Success" ){
            $result = $res['result'];
            if($result['isValid']){
                // get operator product??
                return [
                    "status" => "success",
                    "message" => "Number is valid",
                    "valid" => true,
                    "operators" => $result['operator']['alt'],
                    "operatorId" => $result['operator']['id'],
                    "operatorName" => $result['operator']['name'],
                    "min" => $result['country']['msisdnLength']['min'],
                    "max" => $result['country']['msisdnLength']['max'],
                ];
            }else{
                return [
                    "status" => "error",
                    "message" => "Number is not valid",
                    "valid" => false
                ];
            }
        }else{
            return [
                "status" => "error",
                "message" => "Number is not valid",
                "valid" => false
            ];
        }
    }

    public function buy_topup(Request $request){
        // validate request
        $request->validate([
            'amount' => 'required|numeric|min:200',
            'number' => 'required|string',
            'country' => 'required|string|max:2',
            'operator' => 'required|numeric',
            'pin' => 'required|numeric|digits:4',
        ]);
        $user = Auth::user();
        if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        $msisdn = $request->phone_code.$request->number;
        $ref = getTrans('TOPUP');
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- debit, 3-others
        $trans->code = $ref;
        $trans->message = "Pending {$request->operator_name} Topup of ".format_price($request->amount) .' for '.$msisdn;
        $trans->amount = $request->amount;
        $trans->status = 2;
        $trans->charge = 0;
        $trans->service = "topup";
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $request->amount;
        $trans->save();
        // Create Topup Trx
        $trx = new SochiTrx();
        $trx->user_id = $user->id;
        $trx->operator = $request->operator;
        $trx->operator_name = $request->operator_name;
        $trx->type = "topup"; // 1- airtime, 2- data, 3-swap
        $trx->code = $ref;
        $trx->message = $trans->message ;
        $trx->amount = $request->amount;
        $trx->status = 2; //1 - success , 2- pending, 3 -declined
        $trx->number = $msisdn;
        $trx->new_balance = $user->balance - $request->amount;
        $trx->old_balance = $user->balance;
        $trx->save();
        // deduct balance
        $user->balance = $user->balance - $request->amount;
        $user->save();
        // send request to api
        $payload = [
            'ref' => $ref,
            "operator" => $request->operator,
            'number' => $msisdn,
            'amount' => $request['amount'],
        ];
        $process = new BillProcess();
        $apires = $process->processTopup($payload);
        if(isset($apires['api_status']) && $apires['api_status'] == "success"){
            $trans->message = "Successful {$request->operator_name} Topup of ".$apires['price'] .$apires['price_symbol'] .' to '.$msisdn;
            $trans->status = 1;
            $trans->response = json_encode($apires['response']);
            $trans->save();
            // Finalize sochi Trx
            $trx->message = $trans->message;
            $trx->response = json_encode($apires['response']);
            $trx->status = 1; //1 - success , 2- pendig, 3 -declined
            $trx->api_name = $apires['name'];
            $trx->api_ref = $apires['ref'];
            $trx->operator_symbol = $apires['price_symbol'];
            $trx->operator_price = $apires['price'];
            $trx->save();

            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }
            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $request->amount);
            }

            $res['status'] = "success";
            $res['msg'] = $trans->message;
            return $res;

        }else{
            $trx->new_balance = $trx->old_balance;
            $trans->new_balance = $trx->old_balance;
            $trans->status = 3;
            $trans->response = json_encode($apires);
            $trans->message = "Failed {$request->operator_name} Topup of ".format_price($request->amount) .' for '.$msisdn;
            $trans->save();
            // Create network Trx
            $trx->api_name = $apires['name'];
            $trx->response = json_encode($apires);
            $trx->message = $trans->message;
            $trx->status = 3; //1 - success , 2- pendig, 3 -declined
            $trx->save();
            // refund user
            $user->balance = $user->balance + $request->amount;
            $user->save();
            // cancel transaction
            $res['status'] = "error";
            $res['msg'] = $trans->message;
            return $res;
        }
        return $apires;
    }

    // Giftcards
    public function giftcard()
    {
        if(sys_setting('is_giftcard') != 1){
            return redirect()->back()->withError('Giftcard service is currently disabled. Please try again');
        }
        $giftcard = Giftcard::whereStatus(1)->get();
        return view('bills.giftcard', compact('giftcard'));
    }

    function buy_giftcard(Request $request){
        // validate
        $request->validate([
            'giftcard' => 'required|numeric|exists:giftcards,id',
            'pin' => 'required|digits:4',
        ]);
        $user = Auth::user();
         if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        // get plan
        $plan = Giftcard::find($request->giftcard);
        if ($plan->status != 1){
            $response['status'] = "error";
             $response['msg'] = "This giftcard is currently disabled";
            return $response;
        }
        $cost = $plan->price;
        if ($user->balance < $cost){
            $response['status'] = "error";
           $response['msg'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
           return $response;
        }
        $ref = getTrans('GIFTCARD');
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- debit, 3-others
        $trans->code = $ref;
        $trans->message = "Pending {$plan->value} {$plan->name} giftcard purchase";
        $trans->amount = $cost;
        $trans->status = 2;
        $trans->charge = 0;
        $trans->service = "giftcard";
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $cost;
        $trans->save();
        // Create Topup Trx
        $trx = new SochiTrx();
        $trx->user_id = $user->id;
        $trx->operator = $plan->operator;
        $trx->operator_name = $plan->name .' -'. $plan->value;
        $trx->type = "giftcard"; // 1- airtime, 2- data, 3-swap
        $trx->code = $ref;
        $trx->message = $trans->message ;
        $trx->amount = $cost;
        $trx->status = 2; //1 - success , 2- pending, 3 -declined
        $trx->number = '';
        $trx->new_balance = $user->balance - $cost;
        $trx->old_balance = $user->balance;
        $trx->save();
        // deduct balance
        $user->balance = $user->balance - $cost;
        $user->save();
        // send request to api
        $payload = [
            'ref' => $ref,
            "operator" => $plan->operator,
            "product" => $plan->product,
            'amount' => $plan['amount'],
        ];
        $process = new BillProcess();
        $apires = $process->processGiftcard($payload);
        if(isset($apires['api_status']) && $apires['api_status'] == "success"){
            $trans->message = "Successful purchase of {$plan->value} {$plan->name} giftcard";
            $trans->status = 1;
            $trans->response = json_encode($apires['response']);
            $trans->save();
            // Finalize sochi Trx
            $trx->message = $trans->message;
            $trx->response = json_encode($apires['response']);
            $trx->status = 1; //1 - success , 2- pendig, 3 -declined
            $trx->api_name = $apires['name'];
            $trx->api_ref = $apires['ref'];
            $trx->operator_symbol = $apires['price_symbol'];
            $trx->operator_price = $apires['price'];
            $trx->pin = $apires['pin'];
            $trx->meta = json_encode($apires['meta']);
            $trx->save();
            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }
            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $request->amount);
            }

            $res['status'] = "success";
            $res['msg'] = $trans->message."PIN: ".$apires['pin'];
            return $res;

        }else{
            $trx->new_balance = $trx->old_balance;
            $trans->new_balance = $trx->old_balance;
            $trans->status = 3;
            $trans->response = json_encode($apires);
            $trans->message = "Failed purchase of {$plan->value} {$plan->name} giftcard.";
            $trans->save();
            // Create network Trx
            $trx->api_name = $apires['name'];
            $trx->response = json_encode($apires);
            $trx->message = $trans->message;
            $trx->status = 3; //1 - success , 2- pendig, 3 -declined
            $trx->save();
            // refund user
            $user->balance = $user->balance + $cost;
            $user->save();
            // cancel transaction
            $res['status'] = "error";
            $res['msg'] = $trans->message;
            return $res;
        }
    }

    public function globalData()
    {
        $countries  = Country::whereStatus(1)->orderBy('name')->get();
        if(sys_setting('global_data') != 1){
            return redirect()->back()->withError('International Data service is currently disabled. Please try again');
        }
        return view('bills.global-data', compact('countries'));
    }

    public function getGlobalOperators(Request $request){
        // return $request;
        if($request->serviceType == "data"){
            $type ="dataProducts";
        }else{
            $type = "airtimeProducts";
        }
        $options = "";
        $operators = Operator::whereCountryId($request->country)->has($type)->get();
        if($operators->count() > 0 ){
            foreach($operators as $item){
                $options .= '<option value="' . $item['id'] . '" data-show="' ."show". '" data-fee="' . $item['fee'] . '" data-discount="' . $item['discount'] . '" ' . (old('operator') == $item['id'] ? 'selected' : '') . '>' . $item['name'] . '</option>';
            }
        }
        return '<option value="0" data-price="0" id="0" selected> Select an Operator </option>'.$options;
    }
    public function getGlobalPlans(Request $request){
        // return $request;
        $options = "";
        $products = BillProduct::whereOperatorId($request->operator)->whereType($request->serviceType)->get();
        if($products->count() > 0 ){
            foreach($products as $item){
                $options .= '<option value="' . $item['id'] . '" data-type="' ."{$item['type']}". '" data-price="' . $item['price'] . '" data-min="' . $item['min'] .'" data-max="' . $item['max'] . '" ' . (old('plan') == $item['id'] ? 'selected' : '') . '>' . $item['name'] . '</option>';
            }
        }
        return '<option value="o" data-price="0" id="0" selected> Select a Plan </option>'.$options;
    }

    function buyGlobal(Request $request){
        // validate request
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'number' => 'required|numeric',
            'type' => 'required|string|in:airtime,data',
            'country' => 'required|numeric|exists:countries,id',
            'plan' => 'required|numeric|exists:bill_products,id',
            'operator' => 'required|numeric|exists:operators,id',
            'pin' => 'required|numeric|digits:4',
        ]);
        // get country
        $country = Country::find($request->country);
        $plan = BillProduct::find($request->plan);
        $operator = Operator::find($request->country);
        $msisdn = ltrim($country->prefix.$request->number,'+');
        $user = Auth::user();
        if ($request->pin != $user->trxpin){
            return response()->json([
                'status' => 'error',
                'msg' => 'Wrong Transaction PIN. Try again'
            ]);
        }
        // get type
        if ($request->type == "data"){
            $amount = $plan->price;
            $cost = $plan->price;
            $amount2 = $plan->api;
            $ref = getTrans('DATA');
            $service = "data";
        }else if($request->type == "airtime"){
            $amount = $request->amount;
            $cost = $request->amount + $operator->fee;
            $amount2 = $amount;
            $ref = getTrans('TOPUP');
            $service = "topup";
        }
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- debit, 3-others
        $trans->code = $ref;
        $trans->message = "Pending {$operator->name} {$plan->name}  purchase of ".format_price($amount) .' for '.$msisdn;
        $trans->amount = $amount;
        $trans->status = 2;
        $trans->charge = 0;
        $trans->service = $service;
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $cost;
        $trans->save();
        // Create Topup Trx
        $trx = new SochiTrx();
        $trx->user_id = $user->id;
        $trx->operator = $request->operator;
        $trx->operator_name = $operator->name;
        $trx->type = $service; // 1- airtime, 2- data, 3-swap
        $trx->code = $ref;
        $trx->message = $trans->message ;
        $trx->amount = $amount;
        $trx->status = 2; //1 - success , 2- pending, 3 -declined
        $trx->number = $msisdn;
        $trx->new_balance = $user->balance - $cost;
        $trx->old_balance = $user->balance;
        $trx->save();
        // deduct balance
        $user->balance = $user->balance - $cost;
        $user->save();
        // send request to api
        $payload = [
            'ref' => $ref,
            "operator" => $request->operator,
            "plan" => $request->plan,
            'number' => $msisdn,
            'amount' => $amount2,
        ];
        $process = new BillProcess();
        $apires = $process->processGlobal($payload);
        if(isset($apires['api_status']) && $apires['api_status'] == "success"){
            $trans->message = "Successful {$operator->name} {$plan->name}  purchase of ".$apires['price'] .$apires['price_symbol'] .' to '.$msisdn;
            $trans->status = 1;
            $trans->response = json_encode($apires['response']);
            $trans->save();
            // Finalize sochi Trx
            $trx->message = $trans->message;
            $trx->response = json_encode($apires['response']);
            $trx->status = 1; //1 - success , 2- pendig, 3 -declined
            $trx->api_name = $apires['name'];
            $trx->api_ref = $apires['ref'];
            $trx->operator_symbol = $apires['price_symbol'];
            $trx->operator_price = $apires['price'];
            $trx->save();

            // send trxn email
            if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
                \send_emails($user->email, 'TRX_EMAIL',
                [
                    'username' => $user['username'],
                    'code' => $trans->code,
                    'trx_details' => $trans->message,
                    'trx_type' => trans_type2($trans->type),
                    'amount' => format_price($trans['amount']),
                    'date' => $trans->created_at
                ]);
            }
            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $amount);
            }

            $res['status'] = "success";
            $res['msg'] = $trans->message;
            return $res;

        }else{
            $trx->new_balance = $trx->old_balance;
            $trans->new_balance = $trx->old_balance;
            $trans->status = 3;
            $trans->response = json_encode($apires);
            $trans->message = "Failed {$operator->name} {$plan->name} purchase of ".format_price($amount) .' for '.$msisdn;
            $trans->save();
            // Create network Trx
            $trx->api_name = $apires['name'];
            $trx->response = json_encode($apires);
            $trx->message = $trans->message;
            $trx->status = 3; //1 - success , 2- pendig, 3 -declined
            $trx->save();
            // refund user
            $user->balance = $user->balance + $cost;
            $user->save();
            // cancel transaction
            $res['status'] = "error";
            $res['msg'] = $trans->message;
            return $res;
        }
        return $apires;
    }
}
