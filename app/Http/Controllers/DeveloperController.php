<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Process\BillProcess;
use App\Models\Betsite;
use App\Models\BetTrx;
use App\Models\Bulksms;
use App\Models\CablePlan;
use App\Models\DataBundle;
use App\Models\Decoder;
use App\Models\DecoderTrx;
use App\Models\Education;
use App\Models\EduTrx;
use App\Models\Electricity;
use App\Models\Giftcard;
use App\Models\Network;
use App\Models\NetworkTrx;
use App\Models\SochiTrx;
use App\Models\PowerTrx;
use App\Models\Transaction;
use App\Utility\ApiUtility;
use App\Utility\OpayUtility;
use App\Utility\SochiUtility;
use Auth;
use Http;
use Illuminate\Http\Request;
use Validator;

class DeveloperController extends Controller
{
    //

    function generate_apikey(){
        $user = Auth::user();
        $user->api_key = generate_apikey();
        $user->save();
        return back()->withSuccess('New API Key generated Successfully');
    }

    function user_details(Request $request){
        $user = get_api_user();
        $response['status'] = "success";
        $response['username'] = $user->username;
        $response['api_key'] = $user->api_key;
        $response['refer_bal'] = format_number($user->bonus);
        $response['balance'] = format_number($user->balance);

        return api_response(200,$response);
    }
    function user_transactions(Request $request){
        $user = get_api_user();
        $trx = Transaction::whereUserId($user->id)->orderByDesc('id')->paginate(100);
        foreach ($trx as $item) {
			$trxResults[] = [
				"service"    => short_trx_type($item->service),
				"code"       => $item->code,
				"status"     => trans_status($item->status) ?? null,
				"oldbal"     => $item->old_balance,
				"newbal"     => $item->new_balance,
				"message"    => $item->message,
				"system"     => $item->system,
                "created_at" => $item->created_at
			];
		}
        $res['status'] = "success" ;
        $res['message'] = "Transactions fetched successfully";

        return api_response(201,$res, $trxResults);
    }

    // Buy Data
    function buy_data(Request $request){

        $validator = Validator::make($request->all(), [
            'data_plan' => 'required|numeric',
            'network' => 'required|numeric',
            'phone' => 'required|digits:11',
            'bypass' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ],400);
        }
        if (sys_setting('is_data') != 1){
            $response['status'] = "error";
            $response['message'] = "Data sub is currently disabled.";
            return api_response('400', $response);
        }
        $user = get_api_user();
        $plan = DataBundle::findOrFail($request->data_plan);
        $network = Network::findOrFail($request->network);
        if ($network->data != 1 ) {
            $errorMessage = "Data is currently disabled for this network. Please try again.";
            return [
                'status' => 'error',
                'message' => $errorMessage
            ];
        }
        if ($plan->network_id != $network->id){
            return response()->json([
                'status' => 'error',
                'message' => 'Network and Plan do not match. '
            ],400);
        }
        $price = $plan["api"] ?? $plan->price;
        if ($plan->status != 1 ) {
            $errorMessage = "Data Plan is currently not available. Please try again.";
            return [
                'status' => 'error',
                'message' => $errorMessage
            ];
        }
        // check bypass
        if($request->bypass != true){
            $validate = substr($request->phone, 0, 4);
            if($network['id']=="1"){
                if(strpos(" 0702 0703 0713 0704 0706 0716 0802 0803 0806 0810 0813 0814 0816 0903 0913 0906 0916 0804 ", $validate) == FALSE || strlen($request->phone) != 11){
                    header('HTTP/1.0 400 PHONE NUMBER VALIDATOR');
                    $response['message'] = "This number is not an {$network->name} Number => {$request->phone}";
                    $response['status'] = "error";
                    return api_response('400', $response);
                    return $response;
                }
            }elseif($network['id']=="3"){
                if(strpos(" 0904 0802 0902 0702 0808 0908 0708 0918 0818 0718 0812 0912 0712 0801 0701 0901 0907 0917", $validate) == FALSE || strlen($request->phone) != 11){
                    header('HTTP/1.0 400 PHONE NUMBER VALIDATOR');
                    $response['message'] = "This number is not an {$network->name} Number => {$request->phone}";
                    $response['status'] = "error";
                    return api_response('400', $response);
                    return $response;
                }
            }elseif($network['id']=="2"){
                if(strpos(" 0805 0705 0905 0807 0907 0707 0817 0917 0717 0715 0815 0915 0811 0711 0911  ", $validate) == FALSE || strlen($request->phone) != 11){
                    header('HTTP/1.0 400 PHONE NUMBER VALIDATOR');
                    $response['message'] = "This number is not an {$network->name} Number => {$request->phone}";
                    $response['status'] = "error";
                    return api_response('400', $response);
                    return $response;
                }
            }elseif($network['id']=="4"){
                if(strpos(" 0809 0909 0709 0819 0919 0719 0817 0917 0717 0718 0918 0818 0808 0708 0908  ", $validate) == FALSE || strlen($request->phone) != 11){
                    header('HTTP/1.0 400 PHONE NUMBER VALIDATOR');
                    $response['message'] = "This number is not an {$network->name} Number => {$request->phone}";
                    $response['status'] = "error";
                    return $response;
                }
            }
        }
        if ($user->balance >= $price){
            //create transaction
            $trans = new Transaction();
            $trans->user_id = $user->id;
            $trans->type = 2; // 1- credit, 2- deit, 3-others
            $trans->code = getTrans('DATA');
            $trans->message = 'Pending Purchase of '.$plan->name.' to '. $request['phone'];
            $trans->amount = $price;
            $trans->status = 3;
            $trans->charge = 0;
            $trans->system = "API";
            $trans->service = 2; // bills
            $trans->old_balance = $user->balance;
            $trans->new_balance = $user->balance - $price;
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
            $trans->system = "API";
            $trx->number = $request->phone;
            $trx->new_balance = $user->balance - $price;
            $trx->old_balance = $user->balance;
            $trx->save();
            // deduct balance
            $user->balance = $user->balance - $price;
            $user->save();
            // api transaction
            $data = [
                'amount' => $request->amount,
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

                $response['oldbal'] = $trx->old_balance;
                $response['newbal'] = $trx->new_balance;
                $response['ref'] = $trans['code'];
                $response['response'] = $apires['message'];
                $response['message'] = 'You have successfully purchased '.$plan->name.' to '. $request['phone'];
                $response['status'] = "success";

                return api_response(200,$response);

            }else{
                $trans->message = 'Failed Purchase of '.$plan->name.' to '. $request['phone'];
                $trans->status = 3;
                $trans->response = json_encode($apires['response']);
                $trans->new_balance = $trans->old_balance;
                $trans->save();
                // Create network Trx
                $trx->api_name = $apires['name'];
                $trx->name = 'Failed Purchase of '.$plan->name.' to '. $request['phone'];
                $trx->status = 3; //1 - success , 2- pendig, 3 -declined
                $trx->response = json_encode($apires['response']);
                $trx->new_balance = $trx->old_balance;
                $trx->save();
                // refund user
                $user->balance = $user->balance + $plan->amount;
                $user->save();
                // cancel transaction

                $response['status'] = "error";
                $response['message'] = $trans['message'];
                $response['response'] = $apires['message'];
                $response['ref'] = $apires['ref'];

                return api_response(200,$response);
            }
            $res['status'] = "error";
            $res['message'] = "Something went wrong. Please try again";
            return $res;
        }else{
            $response['status'] = "error";
            $response['message'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
            return response()->json($response, 401);
        }

        $res['status'] = "error";
        $res['message'] = "Something went wrong. Please try again";
        return $res;
    }

    // buy aortime
    function buy_airtime(Request $request){
        $validator = Validator::make($request->all(), [
            'network' => 'required|numeric',
            'phone' => 'required|digits:11',
            'bypass' => 'required',
            'amount' => 'required|min:100|numeric|max:5000'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ],400);
        }
        if (sys_setting('is_airtime') != 1){
            $response['status'] = "error";
            $response['message'] = "Airtime payment is currently disabled.";
            return api_response('400', $response);
        }
        $user = get_api_user();
        $network = Network::findOrFail($request->network);
        if ($network->airtime != 1 ) {
            $errorMessage = "Airtime is currently disabled for this network. Please try again.";
            return [
                'status' => 'error',
                'message' => $errorMessage
            ];
        }
        if($request->bypass != true){
            $validate = substr($request->phone, 0, 4);
            if($network['id']=="1"){
                if(strpos(" 0702 0703 0713 0704 0706 0716 0802 0803 0806 0810 0813 0814 0816 0903 0913 0906 0916 0804 ", $validate) == FALSE || strlen($request->phone) != 11){
                    header('HTTP/1.0 400 PHONE NUMBER VALIDATOR');
                    $response['message'] = "This number is not an {$network->name} Number => {$request->phone}";
                    $response['status'] = "error";
                    return api_response('400', $response);
                    return $response;
                }
            }elseif($network['id']=="3"){
                if(strpos(" 0904 0802 0902 0702 0808 0908 0708 0918 0818 0718 0812 0912 0712 0801 0701 0901 0907 0917", $validate) == FALSE || strlen($request->phone) != 11){
                    header('HTTP/1.0 400 PHONE NUMBER VALIDATOR');
                    $response['message'] = "This number is not an {$network->name} Number => {$request->phone}";
                    $response['status'] = "error";
                    return api_response('400', $response);
                    return $response;
                }
            }elseif($network['id']=="2"){
                if(strpos(" 0805 0705 0905 0807 0907 0707 0817 0917 0717 0715 0815 0915 0811 0711 0911  ", $validate) == FALSE || strlen($request->phone) != 11){
                    header('HTTP/1.0 400 PHONE NUMBER VALIDATOR');
                    $response['message'] = "This number is not an {$network->name} Number => {$request->phone}";
                    $response['status'] = "error";
                    return api_response('400', $response);
                    return $response;
                }
            }elseif($network['id']=="4"){
                if(strpos(" 0809 0909 0709 0819 0919 0719 0817 0917 0717 0718 0918 0818 0808 0708 0908  ", $validate) == FALSE || strlen($request->phone) != 11){
                    header('HTTP/1.0 400 PHONE NUMBER VALIDATOR');
                    $response['message'] = "This number is not an {$network->name} Number => {$request->phone}";
                    $response['status'] = "error";
                    return $response;
                }
            }
        }
        $cost = $request->amount *($network->api_discount /100);
        if ($user->balance < $cost){
            $response['status'] = "error";
            $response['message'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
            return $response;
        }
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
        $trans->system = "API";
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
        $trx->system = "API";
        $trx->number = $request->phone;
        $trx->new_balance = $user->balance - $cost;
        $trx->old_balance = $user->balance;
        $trx->save();
        // deduct user balance
        $user->balance = $user->balance - $cost;
        $user->save();
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

            $response['status'] = "success";
            $response['message'] = $trans['message'];
            $response['oldbal'] = $trx->old_balance;
            $response['newbal'] = $trx->new_balance;
            $response['ref'] = $trans['code'];
            return api_response(201,$response);
            // $res['message'] = "Airtime Purchase of {$network->name} ₦$request->amount to {$request->phone} was successful";

        }else{
            $trx->new_balance = $trx->old_balance;
            $trans->new_balance = $trx->old_balance;
            $trans->status = 3;
            $trans->response = json_encode($apires);
            $trans->save();
            // Create network Trx
            $trans->message = "Failed Purchase of {$network->name} Airtime worth  ".format_price($request->amount) .' for '.$request->phone;
            $trx->name = "Failed Purchase of {$network->name} Airtime worth  ".format_price($request->amount) .' for '.$request->phone;
            $trx->api_name = $apires['name'];
            $trx->response = json_encode($apires);
            $trx->status = 3; //1 - success , 2- pendig, 3 -declined
            $trx->save();
            // refund user
            $user->balance = $user->balance + $cost;
            $user->save();
            // cancel transaction
            $res['status'] = "error";
            $res['message'] = "Airtime Purchase of {$network->name} ₦$request->amount to {$request->phone} was not successful";
            return api_response(201,$res);
        }
        $res['status'] = "error";
        $res['message'] = "Something went wrong. Please try again";
        return api_response(401,$res);
    }

    // buy cable
    function buy_cable(Request $request){
        $validator = Validator::make($request->all(), [
            'cable_plan' => 'required|numeric',
            'cable' => 'required|string',
            'number' => 'required|min:9',
            'customer_name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ],400);
        }
        if (sys_setting('is_cable') != 1){
            $response['status'] = "error";
            $response['message'] = "Cable payment is currently disabled.";
            return api_response('400', $response);
        }
        $user = get_api_user();
        $cableplan = CablePlan::findorFail($request->cable_plan);
        if ($cableplan->cable_id != $request->cable){
            return response()->json([
                'status' => 'error',
                'message' => 'Decoder and Plan do not match. '
            ],400);
        }
        $cable = $cableplan->decoder;
        if ($cable->status != 1 ) {
            $errorMessage = "{$cable->name} is currently not available. Please try again.";
            return [
                'status' => 'error',
                'message' => $errorMessage
            ];
        }
        if ($cableplan->status != 1 ) {
            $errorMessage = "{$cableplan->name} is currently not available. Please try again.";
            return [
                'status' => 'error',
                'message' => $errorMessage
            ];
        }
        $cost = $cableplan->api;
        if ($user->balance < $cost){
            $response['status'] = "error";
            $response['message'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
            return $response;
        }
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- deit, 3-others
        $trans->code = getTrans('CABLE');
        $trans->message = "Pending purchase of  {$cable->name} {$cableplan->name}  to {$request->number}";
        $trans->amount = $cost;
        $trans->status = 2;
        $trans->charge = 0;
        $trans->system = "API";
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
            'customer' => $request['number'],
            'name' => $request['customer_name'] ?? "Bypass User",
            'plan' => $cableplan->id,
            'decoder' => $cable->id,
            'ref' => $trans->code,
        ];
        $process = new BillProcess();
        $apires = $process->purchase_cabletv($data);
        if(isset($apires['api_status']) && $apires['api_status'] == "success"){
            $trans->message = "successfully purchase {$cable->name} {$cableplan->name}  to {$request->number}";
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
            $trx->decoder_id = $cable->id;
            $trx->code = $trans->code;
            $trx->name = "successfully purchase {$cable->name} - {$cableplan->name}  to {$request->number}";
            $trx->message = "successfully purchase {$cable->name} - {$cableplan->name}  to {$request->number}";
            $trx->amount = $cost;
            $trx->status = 1; //1 - success , 2- pending, 3 -declined
            $trx->charge = 0;
            $trx->customer_name = $request->customer_name ?? "Bypass user";
            $trx->number = $request->number;
            $trx->old_balance = $user->balance - $cost;
            $trx->new_balance = $user->balance;
            $trx->api_name = $apires['name'];
            $trx->save();
            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $cost);
            }

            $response['oldbal'] = $trx->old_balance;
            $response['newbal'] = $trx->new_balance;
            $response['message'] = $trans['message'];
            $response['status'] = "success";
            $response['data'] = $apires['response'];
            $response['message'] = $trans['message'];
            return $response;

        }else{
            $trans->new_balance = $user->balance + $cost;
            $trans->message = "Transaction failed for {$cable->name} - {$cableplan->name}  to {$request->number}";
            $trans->status = 3;
            $trans->response = json_encode($apires['response']);
            $trans->save();
            // refund user
            $user->balance = $user->balance + $cost;
            $user->save();

            $res['status'] = "error";
            $res['message'] = $trans->message;
            $res['oldbal'] = $trans->old_balance;
            $res['newbal'] = $trans->old_balance;
            $res['ref'] = $trans['code'];
            return $res;
        }
        $res['status'] = "error";
        $res['message'] = "Something went wrong. Please try again";
        return $res;
    }

    // buy power
    function buy_power(Request $request){
        $validator = Validator::make($request->all(), [
            'disco' => 'required|numeric',
            'meter_type' => 'required|string',
            'meter_number' => 'required|numeric',
            'bypass' => 'nullable',
            'amount' => 'required|min:100|numeric|max:10000'
        ]);
        if ($validator->fails()) {
             return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->all()
            ],400);
        }
        $request['type'] = $request->meter_type;
        $user = get_api_user();
        if (sys_setting('is_electricity') != 1 ) {
            $errorMessage = "Bills Payment is currently not available. Please try again.";
            return [
                'status' => 'error',
                'message' => $errorMessage
            ];
        }
        $disco = Electricity::findOrFail($request->disco);
        $cost = $request->amount + $disco->fee;
        if ($disco->status != 1 ) {
            $errorMessage = "Payment for {$disco->name} is currently not available. Please try again.";
            return [
                'status' => 'error',
                'message' => $errorMessage
            ];
        }
        $meterdata = [
            'disco' => $disco->n3tdata,
            'meter_number' => $request['meter_number'],
            'meter_type' => $request->meter_type,
        ];
        $api = new ApiUtility();
        try{
            $response = Http::get('https://n3tdata.com/api/bill/bill-validation/', $meterdata)->json();;
            if(($response['status'] == "success")){
                $customer_name = $response['name'];
            }else{
                $customer_name = "Bypass User" ;

            }
        }catch(\Exception $e){
            $customer_name = "User Bypass" ;
        }
        if ($user->balance < $cost){
            return response()->json([
                'status' => 'error',
                'message' => "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance),
            ]);
        }
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- deit, 3-others
        $trans->code = getTrans('POWER');
        $trans->message ='Pending transaction '.$request['amount'].' Purchase '. $disco->name.' for '.$request->meter_number;
        $trans->amount = $cost;
        $trans->status = 3;
        $trans->system = "API";
        $trans->charge = $disco->fee;
        $trans->service = 7; // electricity
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $cost;
        $trans->save();
        // Create network Trx
        $trx = new PowerTrx();
        $trx->user_id = $user->id;
        $trx->electricity_id = $disco->id;
        $trx->code = $trans['code'];
        $trx->name = "Pending purchase {$disco->name} - {$request->amount} to {$request->meter_number}";
        $trx->amount = $cost;
        $trx->status = 2; //1 - success , 2- pending, 3 -declined
        $trx->charge = $disco->fee;
        $trx->customer_name = $customer_name;
        $trx->number = $request->meter_number;
        $trx->old_balance = $user->balance - $cost;
        $trx->new_balance = $user->balance;
        $trx->save();
        // deduct balance
        $user->balance = $user->balance - $cost;
        $user->save();

        $data = [
            'name' => $request->customer_name,
            'type' => $request->meter,
            'amount' => $request->amount,
            'service' => $disco['code'],
            'number' => $request->number,
            'disco' => $disco['id'],
            'ref' => $trans->code,
        ];

        $process = new BillProcess();
        $response = $process->purchase_power($data);
        if(isset($response['api_status']) && $response['api_status'] == "success"){
            $token = $response->token ?? "";
            $trans->status = 1;
            $trans->message = "successfully purchase {$disco->name} {$request->amount} to {$request->number}. Token - {$token}";
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
            $trx->api_name = $response['name'];
            $trx->response = json_encode($response);
            $trx->name = "successfully purchase {$disco->name} - {$request->amount} to {$request->number}";
            $trx->status = 1; //1 - success , 2- pending, 3 -declined
            $trx->token = $response['token'] ?? "";
            $trx->response = json_encode($response);
            $trx->save();

            // give referral bonus
            if(sys_setting('is_affiliate') == 1){
                give_affiliate_bonus($user->id, $request->amount);
            }
            $response['disco'] = $request->disco;
            $response['oldbal'] = $trx->old_balance;
            $response['newbal'] = $trx->new_balance;
            $response['customerName'] = $customer_name;
            $response['token'] = $response['token'];
            $response['amount'] = $request['amount'];
            $response['data'] = $response['response'];
            $response['status'] = "success";
            $response['message'] = $trans['message'];

            return api_response('200', $response);
        }else{

            $trans->message = "Failed purchase {$disco->name} - {$request->amount} to {$request->number}";
            $trans->status = 3;
            $trans->new_balance = $trans->old_balance;
            $trans->response = json_encode($response);
            $trans->save();
            $trx->name = "Failed purchase {$disco->name} - {$request->amount} to {$request->number}";
            $trx->status = 3;
            $trx->api_name = $response['name'] ?? "";
            $trx->new_balance = $trans->old_balance;
            // refund user
            $user->balance = $user->balance + $request->amount;
            $user->save();
            // cancel transaction
            $response['disco'] = $request->disco;
            $response['oldbal'] = $trx->old_balance;
            $response['newbal'] = $trx->new_balance;
            $response['customerName'] = $customer_name;
            $response['amount'] = $request['amount'];
            $response['status'] = "success";
            $response['message'] = $trans['message'];
            return api_response('400', $response);

        }
        $res['status'] = "error";
        $res['message'] = "Something went wrong. Please try again";
        return $res;
    }

    // buy education
    function buy_exam(Request $request){
        $validator = Validator::make($request->all(), [
            'exam' => 'required|string',
            'quantity'=> 'required|min:1|numeric|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ],400);
        }
        if (sys_setting('is_education') != 1){
            $response['status'] = "error";
            $response['message'] = "Exam PIN is currently disabled.";
            return api_response('400', $response);
        }
        $user = get_api_user();
        $plan = Education::where('code',$request->exam)->first();
        if(!$plan || $plan->status != 1){
            $response['status'] = "error";
            $response['message'] = "{$request->exam} is Not Avialable";
            return api_response('400', $response);
        }
        $cost = $request->quantity * $plan->api;
        if ($user->balance < $cost){
            $response['status'] = "error";
            $response['message'] = "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance);
            return api_response(400, $response);
        }
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
        $trans->system = "API";
        $trans->service = 6; // education
        $trans->new_balance = $user->balance;
        $trans->old_balance = $user->balance - $cost;
        $trans->save();
         // Create Education Trx
        $trx = new EduTrx();
        $trx->user_id = $user->id;
        $trx->education_id = $plan->id;
        $trx->code = $trans->code;
        $trx->quantity = $request->quantity;
        $trx->name = $plan->name ." code} pin ₦{$cost} Purchase Pending";
        $trx->amount = $cost;
        $trx->status = 2; //1 - success , 2- pending, 3 -declined
        $trx->charge = 0;
        $trx->old_balance = $user->balance - $cost;
        $trx->new_balance = $user->balance;
        $trx->save();

        $data = [
            'name' => strtoupper($plan['code']),
            'exam' => $plan->id,
            'quantity' => $request['quantity'],
            'ref' => $trans->code,
        ];
        $process = new BillProcess();
        $response = $process->purchase_exam($data);
        if(isset($response['api_status']) && $response['api_status']== "success"){
            // create transaction
            $trans->status = 1;
            $trans->message = $plan->name ." pin ₦{$cost} Purchased successfully.";
            $trans->response = json_encode($response);
            $trans->save();
            // Create Education Trx
            $trx->api_name = $response['name'];
            $trx->name = $plan->name ." pin ₦{$cost} Purchase successful";
            $trx->status = 1; //1 - success , 2- pending, 3 -declined
            $trx->response = json_encode($response);
            $trx->pins = $response['pin'];
            $trx->serial = $response['serial'] ?? " ";
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
            $response['exam'] = $plan->code;
            $response['quantity'] = $request->quantity;
            $response['oldbal'] = $trx->old_balance;
            $response['newbal'] = $trx->new_balance;
            $response['amount'] = $cost;
            $response['price'] = $plan->api;
            $response['pin'] = $response['pin'];
            $response['serial'] = $response['serial'];
            $response['status'] = "success";
            $response['message'] = $trans['message'];
            return api_response('200', $response);

        }else{
            // refund user
            $trans->response = json_encode($response);
            $trans->status = 3;
            $trans->new_balance = $trans->old_balance;
            $trans->message = "Transaction fail for ".format_price($cost) ." {$plan->code} PIN";
            $trans->save();
            $trx->api_name = $response['name'];
            $trx->status = 3;
            $trx->name = "Transaction fail for ".format_price($cost) ." {$plan->code} PIN";
            $trx->new_balance = $trans->old_balance;
            $trx->save();
            $user->balance = $user->balance + $cost;
            $user->save();
            // cancel transaction
            $response['status'] = "error";
            $response['message'] = $trans['message'];
            return $response;
        }
    }

    // cable validation
    function cable_validation(Request $request){
        $validator = Validator::make($request->all(), [
            'cable' => 'required|string',
            'number' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ],400);
        }
        $data = [
            'cablename' => strtoupper($request->cable),
            'smart_card_number' => $request['number'],
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
                    'status' => 'error',
                    'message' => "Unable to get Customer Name. Please check and try again",
                ];
            }
        }catch(\Exception $e){
            return [
                'status' => 'error',
                'message' => "Unable to get Customer Name. Please check and try again",
            ];
        }
    }

    // meter validation
    function power_validation(Request $request){

        $validator = Validator::make($request->all(), [
            'disco' => 'required|numeric',
            'meter_type' => 'required|string|in:prepaid,postpaid',
            'meter_number' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ],400);
        }
        $disco = Electricity::findOrFail($request->disco);
        // use sochi
        $slot = new SochiUtility();
        $comm = "execTransaction";
        $payload = [
            "accountId" => $request['meter_number'],
            'productId' => $disco->sochi_product,
        ];
        try{
            $response = $slot->makeRequest($comm, $payload);
            if(isset($api_result['status']) && $api_result['status']['typeName']== "Success"){
                return [
                    'status' => 'success',
                    'name' => $response['result']['customerName'] ?? "",
                    'message' => " Customer Name Validated.",
                ];
            }else{
                return [
                    'status' => 'fail',
                    'message' => "Unable to Validate Customer Name. Please check and try again",
                ];
            }
        }catch(\Exception $e){
            return [
                'status' => 'fail',
                'message' => "Unable to get Customer Name. Please check and try again",
            ];
        }
    }

    // betting validation
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
                    'message' => "Invalid Account ID. Please check and try again",
                ];
            }
        }catch(\Exception $e){
            return [
                'status' => 'fail',
                'message' => "Unable to get Customer Name. Please check and try again",
            ];
        }

    }
    // buy betting
    public function buy_betting(Request $request){
        // return $request;
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'number' => 'required|string',
            'betsite' => 'required|numeric|exists:betsites,id',
            'customer_name' => 'nullable'
        ]);
        if ($request->amount < 0){
            return response()->json([
                'status' => 'error',
                'message' => 'Amount must not be less than #1. Try again'
            ]);
        }
        // $user = Auth::user();
        $user = get_api_user();
        $disco = Betsite::findOrFail($request->betsite);
        $cost = $request->amount + $disco->fee;
        if ($disco->minimum > $request->amount){
            return response()->json([
                'status' => 'error',
                'message' => 'Amount is lower than minimum price '.format_price($disco->minimum),
            ]);
        }
        if ($user->balance < $cost){
            return response()->json([
                'status' => 'error',
                'message' => "Insufficient Balance Fund Your Wallet And Try Again ".format_price($user->balance),
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


            $response['status'] = "success";
            $response['message'] = $trans['message'];
            $response['oldbal'] = $trx->old_balance;
            $response['newbal'] = $trx->new_balance;
            $response['ref'] = $trans['code'];
            return api_response(201,$response);

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
            $res['message'] = "Transaction wasnot successful. Please try again";
            return $res;
        }
        $res['status'] = "error";
        $res['message'] = "Something went wrong. Please try again";
        return $res;
    }

    // Send bulk sms
    public function send_bulksms(Request $request){
        // return $request->all();
        $request->validate([
            'message' => 'required|string',
            'sender' => 'required|string',
            'number' => 'required|string',
        ]);
        // $user = Auth::user();
        $user = get_api_user();
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
        $smsfee = sys_setting('api_bulksms_price');

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

                $response['status'] = "success";
                $response['message'] = $trans['message'];
                $response['oldbal'] = $trx->old_balance;
                $response['newbal'] = $trx->new_balance;
                $response['ref'] = $trans['code'];
                return api_response(201,$response);

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
                $response['message'] = $trans['message'];
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
            $response['message'] = $trans->message;
            return $response;
        }
        return [
            'status' => 'error',
            'message' => 'Something went wrong. Please try again'
        ];
    }

    // get giftcards
    function giftcards(Request $request){
        $giftcards = Giftcard::whereStatus(1)->get();
        $list = [];
        foreach ($giftcards as $item) {
            // Construct account list for each account plan
            $list[] = [
                "name" => $item->name,
                "operator" => $item->operator,
                "product" => $item->product,
                "price" => $item->price,
                "value" => $item->value,
                "country" => $item->desc,
            ];
        }
        return $response = [
            'status' => 'success',
            'message' => 'Giftcard plans fetched successfully',
            'data' => $list,
        ];
    }
    // buy giftcard
    function buy_giftcard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'operator' => 'required|numeric',
            'product' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ],400);
        }
        if (sys_setting('is_giftcard') != 1){
            $response['status'] = "error";
            $response['message'] = "Giftcard is currently disabled.";
            return api_response('400', $response);
        }
        $plan = Giftcard::whereStatus(1)->whereOperator($request->operator)->first();
        if (!$plan){
            $response['status'] = "error";
            $response['msg'] = "This giftcard is currently disabled";
            return $response;
        }
        $user = get_api_user();
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
        $trans->system = "API";
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance - $cost;
        $trans->save();
        // Create Topup Trx
        $trx = new SochiTrx();
        $trx->user_id = $user->id;
        $trx->operator = $plan->operator;
        $trx->operator_name = $plan->name;
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

            $response['status'] = "success";
            $response['oldbal'] = $trx->old_balance;
            $response['newbal'] = $trx->new_balance;
            $response['ref'] = $trans['code'];
            $response['pin'] = $trans['pin'];
            $response['response'] = $apires['message'];
            $response['message'] = $trans->message."PIN: ".$apires['pin'];
            return api_response(200,$response);

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
            $response['status'] = "error";
            $response['oldbal'] = $trx->old_balance;
            $response['newbal'] = $trx->new_balance;
            $response['message'] = $trans['message'];
            $response['response'] = $trans['message'];
            $response['ref'] = $apires['ref'];

            return api_response(200,$response);
        }
    }

    // International topup
    function buy_topup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:200',
            'number' => 'required|string',
            'country' => 'required|string|max:2',
            'operator' => 'required|numeric',
            'operator_name' => 'required|string',
        ]);
        $user = get_api_user();
        $msisdn = $request->number;
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
        $trans->system = "API";
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

            $response['status'] = "success";
            $response['message'] = $trans['message'];
            $response['oldbal'] = $trx->old_balance;
            $response['newbal'] = $trx->new_balance;
            $response['operator_symbol'] = $apires['price_symbol'];
            $response['operator_price'] = $apires['price'];
            return $response;

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
            $response['status'] = "error";
            $response['oldbal'] = $trx->old_balance;
            $response['newbal'] = $trx->new_balance;
            $response['message'] = $trans['message'];
            $response['response'] = $trans['message'];
            $response['ref'] = $apires['ref'];

            return api_response(200,$response);
        }
    }

    // get countries
    function topup_countries(Request $request)
    {
        $countries  = file_get_contents(resource_path('views/countries.json'));
        $countries =  json_decode($countries, true);
        return $response = [
            'status' => 'success',
            'message' => 'Countries fetched successfully',
            'data' => $countries,
        ];
    }

    // validate number
    function topup_validation(Request $request)
    {
        $request->validate([
            'number' => 'required|numeric'
        ]);
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

}
