<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Utility\MonnifyUtility as Monnify;
use App\Utility\PayvesselUtility;
use Bhekor\LaravelFlutterwave\Facades\Flutterwave;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Paystack;

class PaymentController extends Controller
{
     /**
     * Redirect the User to Paystack Payment Page
     * @return Url
     */
    public function initPaystack(Request $request, $details)
    {
        // return $details;
		$callback =  route('paystack.success');
		$ref = getTrans('DEPOSIT');
        // $baseUrl = "https://api.paystack.co";
        // $paystack = new Paystack(env('PAYSTACK_SECRET_KEY'), $baseUrl);
        $details['reference'] = $ref;
        $request->email = $details['email'];
        $request->amount = $details['final'] *100;
        $request->currency = get_setting('currency_code');
        $request->reference = $ref;
        $request->callback_url= $callback ;
        $request->metadata = $details;
        // dd ($request);
        $request->session()->put('payment_data', $details);
        return Paystack::getAuthorizationUrl()->redirectNow();
    }

    public function initFlutter (Request $request, $details){
        $reference = getTrans('DEPOSIT');
        // Enter the details of the payment
        $details['reference'] = $reference;
        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' => $details['final'],
            'email' => $details['email'],
            'tx_ref' => $reference,
            'currency' =>get_setting('currency_code'),
            'redirect_url' => route('flutter.success'),
            'customer' => [
                'email' => $details['email'],
                "phone_number" => $details['phone'],
                "name" => $details['name']
            ],
            'meta' => $details
        ];

        $request->session()->put('payment_data', $details);
        // dd($data);
        $payment = Flutterwave::initializePayment($data);
        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return back()->withError('Unable to Initialize payment');
        }
        return redirect($payment['data']['link']);
        // return $details;
    }

    public function initMonnify (Request $request, $details){
        $monnify = new Monnify();
        $reference = getTrans('DEPOSIT');
        $details['reference'] = $reference;
        $request->session()->put('payment_data', $details);
        $amount = sprintf('%.2f', $details['final']);
        // init transfer
        $data = [
            'amount' => $amount,
            'email' => $details['email'],
            'name' => $details['name'],
            'reference' => $reference,
            'currency' =>get_setting('currency_code'),
            'redirectUrl' => route('monnify.success'),
            'description' => $details['description'],
        ];
        $response =  $monnify->initializePayment($data);

        if (isset($response['responseMessage']) && $response['responseMessage'] == 'success') {
            return redirect($response['responseBody']['checkoutUrl']);
        }
        return back()->withError('Unable to Initialize payment');
    }


    // Flutterwave success
    public function flutter_success(Request $request)
    {
        $status = request()->status;
        $details = $request->session()->get('payment_data');

        //if payment is successful
        if ($status ==  'successful' || $status == 'completed') {
            $transactionID = Flutterwave::getTransactionIDFromCallback();
            $payment = Flutterwave::verifyTransaction($transactionID);
            $details = $payment['data']['meta'];
            // redirect to success url
            $complete = new UserController();
            return $complete->complete_walletDeposit($details, $payment);
        }
        elseif ($status ==  'cancelled'){
            $request->session()->remove('payment_data');
            return redirect()->route('user.wallet')->withError('Payment not successful');
        }
        else{
            $request->session()->remove('impt_data');
            $request->session()->remove('payment_data');
            return redirect()->route('index')->withError('Payment was not Successfull. Please try again');
        }

	}
    // monnify success
    public function monnify_success(Request $request)
    {
        $details = $request->session()->get('payment_data');
		$data = [
			'paymentReference' => $details['reference']
		] ;
        $data1 = $request->all();
        $monnify = new Monnify();
        $response = $monnify->verifyTransaction($data);

        if($response['responseMessage'] == 'success' && $response['responseBody']['paymentStatus'] == "PAID"){
            $complete = new UserController();
            return $complete->complete_walletDeposit($details, $response);
        }
        else {
            $request->session()->remove('payment_data');
            return redirect()->route('user.wallet')->withError('Payment not successful');
        }
    }

    // Paystack Success
    public function paystack_success(Request $request)
    {
        $payment = Paystack::getPaymentData();
        // dd($payment);
        $details = $request->session()->get('payment_data');
        if(!empty($payment['data']) && $payment['data']['status'] == 'success'){
            // return success url
            $details = $payment['data']['metadata'];
            $complete = new UserController();
            return $complete->complete_walletDeposit($details, $payment);
        }
        else{
            $request->session()->remove('payment_data');
            return redirect()->route('user.wallet')->withError('Payment not successful');
        }
    }

    public function monnify_webhook(Request $request)
    {
        // Save response to a text file
        $logFile = 'logs/monnofy_webhook_log.txt';
        $lm = json_encode($request->all(), JSON_PRETTY_PRINT);
        file_put_contents($logFile, $lm, FILE_APPEND);

        // VERIFY tRX
        $monnify = new Monnify();
        $input = $request->all();
        $data = [
			'paymentReference' => $input['eventData']['paymentReference']
		] ;
        $response = $monnify->verifyTransaction($data);

        if($response['responseMessage'] == 'success' && $response['responseBody']['paymentStatus'] == "PAID"){

            if($input['eventData']['paymentMethod'] == "ACCOUNT_TRANSFER"){
                $details['amount'] = $input['eventData']['amountPaid'];
                $details['reference'] = $input['eventData']['product']['reference'];
                $details['final'] = $input['eventData']['settlementAmount'];

                $complete = new UserController();
                return $complete->complete_AutobankDeposit($details, $response);
            }
            return "success";
        }
        return "error";
    }

    public function payvessel_webhook(Request $request)
    {
        // Save response to a text file

        $logFile = 'logs/payvessel-webhook_log.txt';
        $lm = json_encode($request->all(), JSON_PRETTY_PRINT);
        file_put_contents($logFile, $lm, FILE_APPEND);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF exemption
            $payload = file_get_contents('php://input');
            $payvessel_signature = $_SERVER['HTTP_PAYVESSEL_HTTP_SIGNATURE'];
            //this line maybe be differ depends on your server
            //$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $secret = env('PAYVESSEL_SECRET_KEY');
            $hashkey = hash_hmac('sha512', $payload, $secret);

            if ($payvessel_signature == $hashkey && $ip_address == "162.246.254.36") {
                $data = json_decode($payload, true);
                $amount = floatval($data['order']['amount']);
                $settlementAmount = floatval($data['order']['settlement_amount']);
                $fee = floatval($data['order']['fee']);
                $reference = $data['virtualAccount']['virtualAccountNumber'];
                $description = $data['order']['description'];

                $details['amount'] = $amount;
                $details['reference'] = $reference;
                $details['final'] = $settlementAmount;

                $complete = new UserController();
                return $complete->complete_AutobankDeposit2($details, $data);
                // Check if reference already exists in your payment transaction table

            } else {
                echo json_encode(["message" => "Permission denied, invalid hash or ip address."]);
                http_response_code(400);
            }
        } else {
            // Handle other HTTP methods if needed
            echo json_encode(["message" => "Method not allowed"]);
            http_response_code(405);
        }
    }

    public function wema_webhook(Request $request)
    {
        $logFile = 'logs/wema-webhook_log.txt';
        $lm = json_encode($request->all(), JSON_PRETTY_PRINT);
        file_put_contents($logFile, $lm, FILE_APPEND);
        $response = $request->all();
        if(isset($response['transactionType']) && $response['transactionType'] == 'Credit'){
            $details['amount'] = floatval($response['amount']);
            $details['reference'] = $response['accountNumber'];
            $details['final'] = floatval($response['amount']);

            $complete = new UserController();
            return $complete->completeWemaDeposit($details, $response);
        }
        // callback for account generation
        if(isset($response['requestType']) && $response['requestType'] == 2){
            // check if nuban status is active
            $complete = new UserController();
            return $complete->completeWemaAccount($response);
        }

        return "success";

    }
}
