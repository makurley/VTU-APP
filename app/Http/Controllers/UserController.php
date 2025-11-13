<?php

namespace App\Http\Controllers;

use App\Models\DecoderTrx;
use App\Models\Deposit;
use App\Models\EduTrx;
use App\Models\Mdeposit;
use App\Models\Network;
use App\Models\NetworkTrx;
use App\Models\PowerTrx;
use App\Models\{BetTrx, RechargePin, DataPin,Education,Electricity,CablePlan,Decoder, DataBundle, DatacardPlan, SochiTrx};
use App\Models\Transaction;
use App\Models\User;
use App\Utility\MonnifyUtility;
use App\Utility\PayvesselUtility;
use App\Utility\WemaUtility;
use Auth;
use Cache;
use Hash;
use Illuminate\Http\Request;
use Str;


class UserController extends Controller
{
    public function dashboard(){
        $transactions = Transaction::whereUserId(Auth::user()->id)->orderByDesc('updated_at')->limit(20)->get();
        return view('user.index', \compact('transactions'));
    }
    function upgrade_account() {
        $user = Auth::user();
        $price =  sys_setting('reseller_upgrade');
        if($price > $user->balance){
            return back()->withError("Insufficient Balance to Upgrade to Reseller Account. Please fund your Wallet and try again");
        }
        // deduct balance
        $user->balance = $user->balance - $price;
        $user->type = "reseller";
        $user->save();
         // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 2; // 1- credit, 2- deit, 3-others
        $trans->code = getTrxcode(14);
        $trans->message = "Account Upgrade to Reseller account";
        $trans->amount = $price;
        $trans->status = 1;
        $trans->charge = 0;
        $trans->service = "upgrade"; // bills
        $trans->old_balance = $user->balance + $price;
        $trans->new_balance = $user->balance;
        $trans->save();
        // send trx email
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

        return back()->with('success', 'Account Upgraded Successfully');
        // return $user;
    }
    // Verify Account
    public function verify_account(){
        if(Auth::user()->kyc_verify == 1){
            return to_route('user.dashboard')->withSuccess('Your KYC has been verified successfully');
        }
        $monnify = new MonnifyUtility();
        $banks = Cache::get('monnify_banks');
        if (!$banks) {
            $banks = $monnify->getBanks()['responseBody'] ?? [];
            Cache::put('monnify_banks', $banks, 86400);
        }

        return view('user.verify', compact('banks'));
    }
    function verify_kyc(Request $request){
        // validate request
        $request->validate([
            'bvn' => 'required|numeric|digits:11',
        ]);
        $monnify = new MonnifyUtility();
        $user = Auth::user();
        $bvn = $request->bvn;

        // if user has customer account, update it
        if($user->virtual_ref ){
            //update customer details
            $data = ['bvn' => $bvn];
            $updatekyc = $monnify->updateCustomerKyc($user->virtual_ref, $data);
            $lm = json_encode($updatekyc, JSON_PRETTY_PRINT);
            file_put_contents('public/test-monnify.txt', $lm, FILE_APPEND);
            if(isset($updatekyc['responseMessage']) && $updatekyc['responseMessage'] == "success"){
                $user->kyc_verify = 1;
                $user->bvn = $bvn;
                $user->save();
                $sts = 'success';
                $msg = "KYC Validated successfully";
                return to_route('user.dashboard')->withSuccess($msg);
            }
            else{
                // do nothing
                $user->bvn = $bvn;
                $user->kyc_verify = 2;
                $user->save();
                $sts = 'error';
                $msg = "Unable to validate BVN. Please try again";
                return back()->with($sts, $msg)->withInput();
            }

        }else{
            $user->bvn = $bvn;
            $user->kyc_verify = 1;
            $user->save();
            $msg = "KYC Validated successfully";
            return to_route('user.dashboard')->withSuccess($msg);

        }

    }

    // Settings
    public function setting(){
        return view('user.setting');
    }
    public function profile(){
        return view('user.profile');
    }
    function update_profile(Request $request){
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'ref_id' => 'nullable|string',
        ]);
        // return $request;
        $user = Auth::User();
        $user->name = $request->name;
        // check if no user exists with the email and then save
        if(user::where('id','!=', $user->id)->where('phone', $request->phone)->first() == null){
            $user->phone = $request->phone;
        }else{
            return redirect()->back()->withError('Phone Number has been used');
        }
        $user->address = $request->address;
        $user->save();
        return redirect()->back()->withSuccess('Updated successfully');
    }

    function update_ref(Request $request){
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'ref_id' => 'nullable|string',
            'activate' => 'nullable|string'
        ]);
        // return $request;
        $user = Auth::User();
        $user->name = $request->name;
        // check if no user exists with the email and then save
        if(user::where('id','!=', $user->id)->where('ref_id', $request->ref_id)->first() == null){
            $user->ref_id = $request->ref_id;
        }else{
            return redirect()->back()->withError('already Activated');
        }
         $user->ref_id = $request->ref_id;
          $user->activate = $request->activate;
        $user->save();
        return redirect()->back()->withSuccess('Bonus Activated');
    }


    function update_password(Request $request){
        $request->validate([
            'old_password' => 'required|string|min:5',
            'new_password' => 'required|string|min:5'
        ]);
        $user = Auth::User();
        // check if pssword matches
        if(Hash::check($request->old_password, $user->password)){
            $user->password = Hash::make($request->new_password);
            $user->save();
            return redirect()->back()->withSuccess('Password successfully changed');
        }
        return redirect()->back()->withError('Old Password is incorrect');
    }
    // change pin
    function change_pin(Request $request){

        $request->validate([
            'old_pin' => 'required|numeric|min:4',
            'new_pin' => 'required|numeric|min:4'
        ]);
        $user = Auth::User();
        // check if old pin matches
        if($request->old_pin ==  $user->trxpin){
            $user->trxpin = $request->new_pin;
            $user->save();
            return redirect()->back()->withSuccess('Transaction PIN successfully changed');
        }
        return redirect()->back()->withError('Old PIN is incorrect. Contact Admin to change');
    }
    // transactions
    public function transactions(){
        $transactions = Transaction::whereUserId(Auth::user()->id)->orderByDesc('updated_at')->paginate(500);
        return view('user.transactions', \compact('transactions'));
    }
    public function self_service(Request $request){
        $code = $request->code;
        $trx = Transaction::where('code', $code)->first();
        return view('user.self', compact('code', 'trx'));
    }
    public function pricing(){

        $networks = Network::whereStatus(1)->get();
        $dataplans = DataBundle::whereStatus(1)->whereDeleted(0)->get();
        $dcplans = DatacardPlan::whereStatus(1)->whereDeleted(0)->get();
        $decoders = Decoder::whereStatus(1)->get();
        $cable = CablePlan::whereStatus(1)->whereDeleted(0)->get();
        $powers = Electricity::whereStatus(1)->whereDeleted(0)->get();
        $education = Education::whereStatus(1)->whereDeleted(0)->get();

        return view('user.pricing', compact('networks', 'dataplans', 'dcplans', 'cable','decoders', 'powers', 'education'));
    }
    // Referrals
    public function referrals(){
        return view('user.referrals');
    }
    public function referral_withdraw(Request $request){
        $request->validate([
            'amount' => 'required|numeric|min:50'
        ]);        if ($request->amount < 0){
            return back()->with('error', 'You cant complete this transaction');
        }
        $user = Auth::user();
        if ($user->bonus < $request->amount){
            return back()->with('error', 'You dont have enough funds in your referral bonus to withdraw');
        }
        $user->bonus = $user->bonus - $request->amount;
        $user->balance = $user->balance + $request->amount;
        $user->save();
         // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 1; // 1- credit, 2- deit, 3-others
        $trans->code = getTrxcode(14);
        $trans->message = "bonus withdrawal";
        $trans->amount = $request->amount;
        $trans->status = 1;
        $trans->charge = 0;
        $trans->service = 9; // bills
        $trans->old_balance = $user->balance - $request->amount;
        $trans->new_balance = $user->balance;
        $trans->save();
        // send trx email
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
        return back()->with('success', 'Bonus Converted Successfully');
    }
    // Wallet
    public function wallet(){
        $user = Auth::user();
        $deposits = Deposit::whereUserId(Auth::user()->id)->orderByDesc('id')->paginate(100);
        $banks = \json_decode($user->virtual_banks);
        $banks2 = \json_decode($user->payvessel_banks);
        $user->wema_banks= \json_decode($user->wema_banks);

        return view('user.wallet', \compact('deposits','banks','banks2','user'));
    }

    public function fund_wallet(Request $request){
        // validate
        $request->validate([
            'amount' => 'required|min:1',
            'gateway' => 'required|string',
        ]);
        $payment = new PaymentController;
        $user = Auth::user();
        $details['amount'] = $request->amount;
        $details['name'] = $user->name;
        $details['user_id'] = $user->id;
        $details['phone'] = $user->phone;
        $details['description'] = "Wallet Funding Payment";
        $details['gateway'] = $request->gateway;
        $details['email'] = $user->email;
        // add data to session
        $request->session()->put('payment_data', $details);
        if($request->gateway == "paystack"){
            $details['final'] = $details['amount'] + ( (sys_setting('card_fee')* $request->amount )/100);
            return $payment->initPaystack($request, $details);
        }elseif($request->gateway == "flutter"){
            $details['final'] = $details['amount'] + ( (sys_setting('card_fee')* $request->amount )/100);
            return $payment->initFlutter($request, $details);
        }elseif($request->gateway == "monnify"){
            $details['final'] = $details['amount'] + ( (sys_setting('card_fee')* $request->amount )/100);
            return $payment->initMonnify($request, $details);
        }elseif($request->gateway == "bank"){
            $details['final'] = $details['amount'] - sys_setting('bank_fee');
            $request->session()->put('payment_data', $details);
            return view('payment.bank', compact('details'));
        }
        $request->session()->remove('payment_data');
        return back()->withError('Invalid request. Please try again');
    }

    function complete_walletDeposit($details, $response = null)
    {
        // return $response;
        //check if reference doesnt exist
        $ref = $details['reference'];
        if(Deposit::where('trx', $ref)->first()){
            return redirect()->route('user.wallet')->withError('Payment was not successful');
        }
        $user = User::findOrFail($details['user_id']);
        // save to deposit
        $messg = "You have successfully fund your wallet with N{$details['amount']}.";
        $deposit = new Deposit();
        $deposit->user_id = $details['user_id'];
        $deposit->type = 'card'; // 1- event, 2- form, 3-vote
        $deposit->gateway = $details['gateway'];
        $deposit->trx = $details['reference'] ?? getTrans('DEPOSIT');
        $deposit->message = $messg;
        $deposit->amount = $details['amount'];
        $deposit->response = json_encode($response);
        $deposit->status = 1;
        $deposit->save();
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $details['user_id'];
        $trans->type = 1; // 1- credit, 2- debit, 3-others
        $trans->code = \getTrans('DEPOSIT');
        $trans->message = $messg;
        $trans->amount =$details['amount'];
        $trans->status = 1;
        $trans->charge = 0;
        $trans->service = 9;
        $trans->response = json_encode($response);
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance + $details['amount'];
        $trans->save();
        // Add User Balance
        $user->balance =  $trans['new_balance'];
        $user->save();

        // send email
        if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
            \send_emails($user->email, 'DEPOSIT_EMAIL',
            [
                'username' => $user['username'],
                'amount' => \format_price($deposit->amount),
                'method' => $deposit['gateway'],
                'date' => $trans->created_at
            ]);
        }
        session()->remove('payment_data');
        return redirect()->route('user.wallet')->withSuccess('Payment was successful');
    }
    // Deposits
    public function deposits(){
        return view('user.deposits');
    }

    // Manual PAyment
    function manual_payment(Request $request)
    {
        $details = $request->session()->get('payment_data');
        $user = Auth::user();
        $request->validate([
            'document' => 'required|mimes:png,jpg,jpeg',
            'name' => 'required|string',
        ]);
        $mpayment = new Mdeposit();
        $mpayment->user_id = $user->id;
        $mpayment->name = $request->name;
        $mpayment->amount = $details['amount'];
        $mpayment->code = getTrx(10);
        $mpayment->message = $details['description'];
        // upload document
        if ($request->hasFile('document')){
            $document = $request->file('document');
            $name = Str::random(22).'.jpg';
            $document->move(public_path('uploads/payment'),$name);
            $mpayment->image = "payment/".$name;
        }
        $mpayment->status =2;
        $mpayment->save();
        return redirect()->route('user.wallet')->withSuccess("Your Payment has been submited. Wallet will be funded once your payment has been confirmed by the admin");
    }
    function complete_AutobankDeposit($details, $response = null)
    {
        $user = User::where('virtual_ref', $details['reference'])->first();
        $ref = getTrans('DEPOSIT');
        if($user == null){
            return 'wrong user';
        }
        $fee = sys_setting('auto_fee');
        $charge = ($fee * $details['amount'])/100;
        if($charge > sys_setting('auto_cap')){
            $charge = sys_setting('auto_cap');
        }

        $messg = "You have successfully fund your wallet with N{$details['amount']}.";
        // save to deposit
        $deposit = new Deposit();
        $deposit->user_id = $user->id;
        $deposit->type = 'bank'; // 1- event, 2- form, 3-vote
        $deposit->gateway = "autobank";
        $deposit->trx = $ref;
        $deposit->message = $messg;
        $deposit->amount = $details['amount'] - $charge;
        $deposit->status = 1;
        $deposit->response = json_encode($response);
        $deposit->save();
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 1; // 1- credit, 2- debit, 3-others
        $trans->code = $ref;
        $trans->message = $deposit['message'];
        $trans->amount = $deposit['amount'];
        $trans->status = 1;
        $trans->charge = $charge;
        $trans->service = 9;
        $trans->response = json_encode($response);
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance + $deposit['amount'];
        $trans->save();
        // Add User Balance
        $user->balance =  $trans['new_balance'];
        $user->save();

        // send email
        if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
            \send_emails($user->email, 'DEPOSIT_EMAIL',
            [
                'username' => $user['username'],
                'amount' => \format_price($deposit->amount),
                'method' => "autobank",
                'date' => $trans->created_at
            ]);
        }
        return "success";
    }
    function complete_AutobankDeposit2($details , $response = null)
    {
        $user = User::where('payvessel_ref', $details['reference'])->first();
        $ref = getTrans('DEPOSIT');
        if($user == null){
            return 'wrong user';
        }
        $fee = sys_setting('auto_fee');
        $charge = ($fee * $details['amount'])/100;
        if($charge > sys_setting('auto_cap')){
        }
        $charge = sys_setting('auto_fee2');
        // save to deposit
        $deposit = new Deposit();
        $deposit->user_id = $user->id;
        $deposit->type = 'bank'; // 1- event, 2- form, 3-vote
        $deposit->gateway = "autobank";
        $deposit->trx = $ref;
        $deposit->message = "Autobank wallet funding";
        $deposit->amount = $details['amount'] - $charge;
        $deposit->status = 1;
        $deposit->response = json_encode($response);
        $deposit->save();
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 1; // 1- credit, 2- debit, 3-others
        $trans->code = $ref;
        $trans->message = $deposit['message'];
        $trans->amount = $deposit['amount'];
        $trans->status = 1;
        $trans->charge = $charge;
        $trans->service = 9;
        $trans->old_balance = $user->balance;
        $trans->response = json_encode($response);
        $trans->new_balance = $user->balance + $deposit['amount'];
        $trans->save();
        // Add User Balance
        $user->balance +=  $deposit['amount'] ;
        $user->save();

        // send email
        if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
            \send_emails($user->email, 'DEPOSIT_EMAIL',
            [
                'username' => $user['username'],
                'amount' => \format_price($deposit->amount),
                'method' => "autobank",
                'date' => $trans->created_at
            ]);
        }
        return "success";
    }
    // wema deposits
    function completeWemaDeposit($details , $response = null)
    {
        $user = User::where('wema_ref', $details['reference'])->first();
        $ref = getTrans('DEPOSIT');
        if($user == null){
            return 'wrong user';
        }
        $fee = sys_setting('wema_fee');
        $charge = ($fee * $details['amount'])/100;
        if($charge > sys_setting('wema_cap')){
            $charge = sys_setting('wema_cap');
        }

        // save to deposit
        $deposit = new Deposit();
        $deposit->user_id = $user->id;
        $deposit->type = 'bank'; // 1- event, 2- form, 3-vote
        $deposit->gateway = "autobank";
        $deposit->trx = $ref;
        $deposit->message = "Autobank wallet funding";
        $deposit->amount = $details['amount'] - $charge;
        $deposit->status = 1;
        $deposit->response = json_encode($response);
        $deposit->save();
        // create transaction
        $trans = new Transaction();
        $trans->user_id = $user->id;
        $trans->type = 1; // 1- credit, 2- debit, 3-others
        $trans->code = $ref;
        $trans->message = $deposit['message'];
        $trans->amount = $deposit['amount'];
        $trans->status = 1;
        $trans->charge = $charge;
        $trans->service = 9;
        $trans->response = json_encode($response);
        $trans->old_balance = $user->balance;
        $trans->new_balance = $user->balance + $deposit['amount'];
        $trans->save();
        // Add User Balance
        $user->balance +=  $deposit['amount'];
        $user->save();

        // send email
        if(\sys_setting('trx_email') == 1 && $user->email_notify == 1){
            \send_emails($user->email, 'DEPOSIT_EMAIL',
            [
                'username' => $user['username'],
                'amount' => \format_price($deposit->amount),
                'method' => "autobank",
                'date' => $trans->created_at
            ]);
        }
        return "success";
    }
    // Accounts
    public function bank_accounts(){
        $user = Auth::user();
        if($user->kyc_verify != 1){
            $message = "Please Verify Your BVN to continue enjoying all our Exciting Offers.";
            // Create custom message later
            return to_route('user.verify')->withEmodal($message)->withError($message);
        }
        if($user->virtual_ref == null){
            try {
                $this->generate_bank();
                return back()->withSuccess('Virtual Account Generated Successfully');
            } catch (\Exception $e) {
            // dd($e);
                // return redirect()->route('user.wallet')->withSuccess('Virtual Account not Generated');
            }
        }
        $banks = $user->virtual_banks;
        $banks = \json_decode($banks);
        $banks2 = \json_decode($user->banks2);
        $user->wema_banks= \json_decode($user->wema_banks);

        return view('user.bank', \compact('banks','banks2', 'user'));
    }
    // generate bank accout
    function generate_bank()
    {
        $user = Auth::user();
        $monnify = new MonnifyUtility();
        $data = [
            'email' => $user['email'],
            'name' => $user['name'],
            'bvn' => $user['bvn'],
            'currency' =>get_setting('currency_code'),
            'reference' => $user['username'].\getTrx(4)
        ];
        $response = $monnify->reserveAccount($data);
        if($response['responseMessage'] == 'success'){
            $banks = $response['responseBody']['accounts'];
            $user->virtual_ref = $data['reference'];
            $user->virtual_banks = $banks;
            $user->save();
        }else{
            return;
        }
        // return redirect()->route('user.wallet')->withSuccess('Virtual Account Generated');
    }
    function generate_vessel_bank()
    {
        $user = Auth::user();
        $monnify = new PayvesselUtility();
        $data = [
            'email' => $user['email'],
            'phone' => $user['phone'],
            'name' => $user['username'],
            'currency' =>get_setting('currency_code'),
            'reference' => $user['username'].\getTrx(10)
        ];
        $response = $monnify->reserveAccount($data);
        if($response['status'] == true && $response['service'] == "CREATE_VIRTUAL_ACCOUNT"){
            $banks = $response['banks'];
            $user->payvessel_ref = $response['banks'][0]['accountNumber'];
            $user->payvessel_banks = $banks;
            $user->save();
        }else{
            return redirect()->route('user.wallet')->withError('Virtual Account not Generated. Please try again');
        }
        return redirect()->route('user.wallet')->withSuccess('Virtual Account Generated');
    }
    // generate wema account
    function generateWema(Request $request)
    {
        $request->validate([
            'phoneNumber' => 'required',
            'nin' => 'required|numeric',
        ]);
        $user = Auth::user();
        // check if number belogs to other users
        $existingUser = User::where('id', '!=', $user->id)->where('phone', $request->phoneNumber)->first();
        if(!$existingUser){
            $user->phone = $request->phoneNumber;
            $user->save();
        } else{
            return response()->json([
                'status' => 'error',
                'message' => 'Number is already chosen'
            ]);
        }
        $data = [
            'email' => $user->email,
            'phoneNumber' => $request->phoneNumber,
            'nin' => $request->nin,
        ];
        $wemaUtility = new WemaUtility();
        $response = $wemaUtility->accountRequest($data);

        if (isset($response['data']['trackingId'])) {
            // Save tracking ID to user
            $user->wema_track = $response['data']['trackingId'];
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => "Otp Code was sent to phone number: {$request->phoneNumber}",
                'trackingId' => $response['data']['trackingId']
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => $response['message'] ?? 'Wrong Details Provided'
        ], 400);

    }
    function wemaOtpPage (){
        $user = Auth::user();
        // if user doesn't have ref, return back to account page
        if($user->wema_track == null){
            return to_route('user.accounts')->withError('Please Resubmit Account Details');
        }
        return view('user.otp');
    }

    // otp page
    public function validateNinOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required',
            'phoneNumber' => 'required',
        ]);

        $user = Auth::user();
        $data = [
            'otp' => $request->otp,
            'trackingId' => $user->wema_track,
            'phoneNumber' => $request->phoneNumber,
        ];
        $wemaUtility = new WemaUtility();
        $response = $wemaUtility->validateNinWithOtp($data);

        if (isset($response['data']['accountGenerationStatus']) && $response['data']['accountGenerationStatus'] == 'Pending') {
            $user->wema_status = 2;
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => $response['message'] ?? 'Account creation is in progress'
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => $response['message'] ?? 'Error validating OTP'
        ], 400);
    }


    function completeWemaAccount($response)
    {
        if(isset($response['data']['nubanStatus']) && $response['data']['nubanStatus'] == "Active"){
            // get user with tracking  id
            $user = User::where('email', $response['data']['email'])->first();
            if($user){
                // save account to the database
                $user->wema_banks = $response['data'];
                $user->wema_ref = $response['data']['nuban'];
                $user->wema_status = 1; //set status to active
                $user->save();
                return "success";
            }
        }

        return response('error', 400);
    }
    // report logs
    function airtime_logs()
    {
        $trx = NetworkTrx::whereUserId(Auth::user()->id)->whereType(1)->orderByDesc('id')->paginate(200);
        return view('user.report.airtime', compact('trx'));
    }
    function data_logs()
    {
        $trx = NetworkTrx::whereUserId(Auth::user()->id)->whereType(2)->orderByDesc('id')->paginate(200);
        return view('user.report.data', compact('trx'));
    }
    function swap_logs()
    {
        $trx = NetworkTrx::whereUserId(Auth::user()->id)->whereType(3)->orderByDesc('id')->paginate(200);
        return view('user.report.swap', compact('trx'));
    }
    function power_logs()
    {
        $trx = PowerTrx::whereUserId(Auth::user()->id)->orderByDesc('id')->paginate(200);
        return view('user.report.power', compact('trx'));
    }
    function decoder_logs()
    {
        $trx = DecoderTrx::whereUserId(Auth::user()->id)->orderByDesc('id')->paginate(200);
        return view('user.report.decoder', compact('trx'));
    }
    function bet_logs()
    {
        $trx = BetTrx::whereUserId(Auth::user()->id)->orderByDesc('id')->paginate(200);
        return view('user.report.bet', compact('trx'));
    }
    function education_logs()
    {
        $trx = EduTrx::whereUserId(Auth::user()->id)->orderByDesc('id')->paginate(200);
        return view('user.report.education', compact('trx'));
    }
    function printed_cards()
    {
        $trx = RechargePin::whereUserId(Auth::user()->id)->orderByDesc('id')->paginate(200);

        return view('user.report.voucher', compact('trx'));
    }
    function view_voucher($id)
    {
        $trx = RechargePin::whereUserId(Auth::user()->id)->where('id', $id)->first();
        if(!$trx){
            return back()->withError('Invalid request. Dont be a thief');
        }
        $pins = \json_decode($trx->pins);
        // dd($pins);
        return view('user.report.pins', compact('trx', 'pins'));
    }
    function datapin_logs()
    {
        $trx = DataPin::whereUserId(Auth::user()->id)->orderByDesc('id')->paginate(200);

        return view('user.report.datacard', compact('trx'));
    }
    function view_datacard($id)
    {
        $trx = DataPin::whereUserId(Auth::user()->id)->where('id', $id)->first();
        if(!$trx){
            return back()->withError('Invalid request. Dont be a thief');
        }
        $pins = explode(',',json_decode($trx->pins));
        $serial = explode(',', json_decode($trx->serial));
        $res = \json_decode($trx->response);
        // dd($res);
        return view('user.report.datapins', compact('trx','serial','pins','res'));
    }
    function utility_logs ()
    {
        $trx = Transaction::where('user_id', Auth::user()->id)->whereIn('service', ['tax', 'translog', 'relinst'])->orderByDesc('id')->paginate(200);
        return view('user.report.utility', compact('trx'));
    }
    function giftcard_logs (){
        $trx = SochiTrx::whereUserId(Auth::user()->id)->where('type', 'giftcard')->orderByDesc('id')->paginate(100);
        return view('user.report.giftcard', compact('trx'));
    }
    function topup_logs(){
        $title = "International Airtime";
        $trx = SochiTrx::whereUserId(Auth::user()->id)->where('type', 'topup')->orderByDesc('id')->paginate(100);
        return view('user.report.topup', compact('trx','title'));
    }
    function globaldata_logs(){
        $title = "International Data";
        $trx = SochiTrx::whereUserId(Auth::user()->id)->where('type', 'data')->orderByDesc('id')->paginate(100);
        return view('user.report.topup', compact('trx','title'));
    }
}
