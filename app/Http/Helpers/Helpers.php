<?php

use App\Mail\MainEmail;
use App\Models\{
    ApiSetting,
    EmailTemplate,
    Setting,
    SystemSetting,
    User
};
use App\Utility\GladApi;
use App\Utility\LegitwayUtility;
use App\Utility\MaskaUtility;
use App\Utility\N3tdataUtility;

if (!function_exists('get_setting')) {
    function get_setting($key)
    {
        $settings = Setting::first();
        $setting = $settings->$key;
        return $setting;
    }
}
if (!function_exists('sys_setting')) {
    function sys_setting($key, $default = null)
    {
        $settings = SystemSetting::all();
        $setting = $settings->where('name', $key)->first();

        return $setting == null ? $default : $setting->value;
    }
}
if (!function_exists('api_setting')) {
    function api_setting($key, $default = null)
    {
        $settings = ApiSetting::all();
        $setting = $settings->where('name', $key)->first();

        return $setting == null ? $default : $setting->value;
    }
}

if (!function_exists('static_asset')) {
    function static_asset($path, $secure = null)
    {
        return app('url')->asset('public/assets/' . $path, $secure);
    }
}

if (!function_exists('static_asset3')) {
    function static_asset3($path, $secure = null)
    {
        return app('url')->asset('public/asset3/' . $path, $secure);
    }
}

//return file uploaded via uploader
if (!function_exists('my_asset')) {
    function my_asset($path, $secure = null)
    {
        return app('url')->asset('public/uploads/' . $path, $secure);
    }
}

function text_trim($string, $length = null)
{
    if (empty($length)) $length = 100;
    return Str::limit($string, $length, "...");
}
function show_datetime($date, $format = 'd M, Y h:ia')
{
    return \Carbon\Carbon::parse($date)->format($format);
}

// Transactions
function getTrans($id)
{
    $characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ1234567890acdefghijklmopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 15; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $id.'_'.$randomString;
}
// random string
function getTrxcode($length)
{
    $characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ1234567890';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function getTrx($length)
{
    $characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ1234567890acdefghijklmopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
// short code replacer
function shortCodeReplacer($shortCode, $replace_with, $template_string)
{
    return str_replace($shortCode, $replace_with, $template_string);
}

function show_date($date, $format = 'd M, Y')
{
    return \Carbon\Carbon::parse($date)->format($format);
}

function show_time($date, $format = 'h:ia')
{
    return \Carbon\Carbon::parse($date)->format($format);
}
// Transaction type
function short_trx_type($id){
    if($id == 1){
        return "airtime";
    }elseif($id == 2){
        return "data";
    }elseif($id == 3){
        return "airtime swap";
    }elseif($id == 4){
        return "voucher pin";
    }elseif($id == 5){
        return "cable tv";
    }elseif($id == 6){
        return "education";
    }elseif($id == 7){
        return "electricity";
    }elseif($id == 8){
        return "bulk sms";
    }elseif($id == 9){
        return "wallet";
    }elseif($id == 10){
        return "bonus";
    }elseif($id == 11){
        return "datacard";
    }elseif($id == 12){
        return "betting";
    } else{
        return $id;
    }
}
function trans_status($id){
    if($id == 1){
        return 'successful';
    }elseif($id == 2){
        return 'processing';
    } elseif($id == 3){
        return 'failed';
    } elseif($id == 4){
        return 'reversed';
    } else{
        return $id;
    }
}
function trans_type($id){
    if($id == 1){
        return '<span class="badge bg-success">credit</span>';
    }elseif($id == 2){
        return '<span class="badge bg-danger">debit</span>';
    } else{
        return $id;
    }
}
function trans_type2($id){
    if($id == 1){
        return 'credit';
    }elseif($id == 2){
        return 'debit';
    } else{
        return $id;
    }
}
//formats currency
if (!function_exists('format_price')) {
    function format_price($price)
    {
        $fomated_price = number_format($price, 2);
        $currency = get_setting('currency');
        return $currency .$fomated_price;
    }
}
function sym_price($price)
{
    $fomated_price = number_format($price, 2);
    $currency = get_setting('currency_code');
    return $currency . $fomated_price;
}
function format_number($price)
{
    $fomated_price = number_format($price, 2);
    return $fomated_price;
}

// Send general emails
function send_emails($email, $type, $shortCodes = [])
{
    $email_template = EmailTemplate::whereType($type)->first();
    if($email_template == null){
        return;
    }
    $message = $email_template->content;
    foreach ($shortCodes as $code => $value) {
        $message = shortCodeReplacer('{{'.$code.'}}', $value, $message);
    }
    // subject
    $subject = $email_template->subject;
    foreach ($shortCodes as $code => $value) {
        $subject = shortCodeReplacer('{{'.$code.'}}', $value, $subject);
    }

    // dd($subject, $message);
    // send email
    $data['subject'] = $subject;
    $data['message'] = $message;

    try {
        Mail::to($email)->queue(new MainEmail($data));
    } catch (\Exception $e) {
        // dd($e);
    }

}
// send email
function general_email($email, $mes, $sub)
{
    // return $email;
    $data['subject'] = $sub;
    $data['message'] = $mes;
    try {
        Mail::to($email)->queue(new MainEmail($data));
    } catch (\Exception $e) {
        // dd($e);
    }
}

// give affiliate bonus
function give_affiliate_bonus($id, $amount){
    $user = User::find($id);
    $refer = User::find($user->ref_id);
    $commission = sys_setting('referral_commission') * $amount /100;
    $trxcode = getTrx(12);
    if($refer){
        $refer->bonus = $commission + $refer->bonus;
        $refer->save();
        $refer->transactions()->create([
            'amount' => $commission,
            'user_id' => $refer->id,
            'charge' => 0,
            'old_balance' => $refer->bonus - $commission,
            'new_balance' => $refer->bonus,
            'type' => 1,
            'status'=> 1,
            'service' => 10,
            'message' => 'Referral Bonus from '. $user->username,
            'code' => $trxcode,
        ]);
        // send email
        if(\sys_setting('trx_email') == 1 && $refer->email_notify == 1){
            \send_emails($refer->email, 'TRX_EMAIL',
            [
                'username' => $refer['username'],
                'code' => $trxcode,
                'trx_details' => 'Referral Bonus from '. $user->username,
                'trx_type' => trans_type2(1),
                'amount' => format_price($commission),
                'date' => date('Y-m-d H:m:s')
            ]);
        }
    }
    return;
}

// Install copy files
function install_files($name)
{
    $file = base_path('storage/install/'.$name);
    return $file;
}

// get rechargepinn ID
function rechargepin_id($network, $value){
    if($network == "MTN" && $value == "100"){
        return 1;
    }
    else if($network == "GLO" && $value == "100"){
        return 2;
    }
    else if($network == "AIRTEL" && $value == "100"){
        return 3;
    }
    else if($network == "9MOBILE" && $value == "100"){
        return 4;
    }
    else if($network == "MTN" && $value == "200"){
        return 5;
    }
    else if($network == "GLO" && $value == "200"){
        return 6;
    }
    else if($network == "AIRTEL" && $value == "200"){
        return 7;
    }
    else if($network == "MTN" && $value == "500"){
        return 8;
    }
    else if($network == "MTN" && $value == "1000"){
        return 9;
    }
    else if($network == "GLO" && $value == "500"){
        return 10;
    }
    else if($network == "AIRTEL" && $value == "500"){
        return 11;
    }
    else if($network == "9MOBILE" && $value == "200"){
        return 12;
    }else{
        return 0;
    }

}
function text_shortener($string, $length = null)
{
    if (empty($length)) $length = 100;
    return Str::limit($string, $length, "...");
}

function slug($string)
{
    return Illuminate\Support\Str::slug($string);
}

function n3tdata_balance(){
    $slot = new N3tdataUtility();
    $bal = $slot->getUser();
    return $bal['balance'] ?? 0;
}
function glad_balance(){
    $slot = new GladApi();
    $bal = $slot->getUser();
    return $bal['user']['wallet_balance'] ?? 0;
}
function legit_balance(){
    $slot = new LegitwayUtility();
    $bal = $slot->getUser();
    return $bal['balance'] ?? 0;
}
function maska_balance(){
    $slot = new MaskaUtility();
    $bal = $slot->getUser();
    return $bal['user']['wallet_balance'] ?? 0;
}

function generate_apikey(){
    return bin2hex(openssl_random_pseudo_bytes(33));
}

function api_response($code, $data, $data2=null)
{
    $response = $data;
    $response['data'] = $data2;

    return response()->json($response,$code);
}

function get_api_user(){
    $headers = getallheaders();
    $apikey = $headers['Authorization'];
    $apikey = str_replace('Token ', '', $apikey);
    $user = User::where('api_key', $apikey)->whereBlocked(0)->first();
    return $user;
}


function formatAndValidateUsername($username)
{
    // Remove leading and trailing spaces
    $username = trim($username);

    // Replace consecutive spaces with a single space
    $username = preg_replace('/\s+/', ' ', $username);

    // Remove any special characters except underscores and dashes
    $username = preg_replace('/[^a-zA-Z0-9_-]/', '', $username);

    // Convert spaces to underscores
    $username = str_replace(' ', '_', $username);

    // Validate the username length
    if (strlen($username) < 3 || strlen($username) > 20) {
        return false;
    }

    // Validate the username format using a regular expression
    $pattern = '/^[a-zA-Z][a-zA-Z0-9_-]*$/';
    if (!preg_match($pattern, $username)) {
        return false;
    }

    return $username;
}
