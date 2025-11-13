<?php

namespace App\Http\Controllers\Process;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Utility\{
    ApiUtility, Bulksmsng, FlutterUtility, N3tdataUtility,MaskaUtility, GladApi,
    LegitwayUtility,
    OpayUtility,
    SochiUtility,
    VtuUtility
};
use App\Models\{
    Betsite,
    CablePlan,
    DataBundle,
    DatacardPlan,
    Decoder,
    DecoderTrx,
    Education,
    EduTrx,
    Electricity,
    Network,
    Operator,
    BillProduct,
    RechargePin,
    Transaction
};

class BillProcess extends Controller
{
    //
    function purchase_airtime($data){

        $network = Network::find($data['network']);
        // glad
        if( $network->id == 1 && api_setting('mtn_airtime') == "glad" || $network->id == 2 && api_setting('glo_airtime') == "glad" ||$network->id == 3 && api_setting('airtel_airtime') == "glad" ||$network->id == 4 && api_setting('mob_airtime') == "glad" ) {
            $slot = new GladApi();
            $payload = [
                'network' => $network->glad,
                'mobile_number' => $data['phone'],
                'airtime_type' => "VTU",
                'Ported_number' => true,
                'amount' => $data['amount'],
            ];
            $api_result = $slot->buyAirtime($payload);
            if(isset($api_result['Status']) && $api_result['Status']== "successful"){
                return $res = [
                    'name' => "glad",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                   'response' => $api_result,
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "glad",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        // 3tdata
        else if($network->id == 1 && api_setting('mtn_airtime') == "n3tdata" || $network->id == 2 && api_setting('glo_airtime') == "n3tdata" || $network->id == 3 && api_setting('airtel_airtime') == "n3tdata" || $network->id == 4 && api_setting('mob_airtime') == "n3tdata") {
            $slot = new N3tdataUtility();
            $payload = [
                'network' => $network->n3tdata,
                'phone' => $data['phone'],
                'plan_type' => "VTU",
                'bypass' => true,
                'request-id' => $data['ref'],
                'amount' => $data['amount'],
            ];
            $api_result = $slot->buyAirtime($payload);
            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'name' => "n3tdata",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'message' => $api_result['message'],
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "netdata",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        else if($network->id == 1 && api_setting('mtn_airtime') == "legit" || $network->id == 2 && api_setting('glo_airtime') == "legit" || $network->id == 3 && api_setting('airtel_airtime') == "legit" || $network->id == 4 && api_setting('mob_airtime') == "legit") {
            $slot = new LegitwayUtility();
            $payload = [
                'network' => $network->legit,
                'phone' => $data['phone'],
                'plan_type' => "VTU",
                'bypass' => true,
                'request-id' => $data['ref'],
                'amount' => $data['amount'],
            ];
            $api_result = $slot->buyAirtime($payload);
            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'name' => "legit",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'message' => $api_result['message'],
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "legit",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        // maska
        else if($network->id == 1 && api_setting('mtn_airtime') == "maska" || $network->id == 2 && api_setting('glo_airtime') == "maska" || $network->id == 3 && api_setting('airtel_airtime') == "maska" || $network->id == 4 && api_setting('mob_airtime') == "maska" ) {
            $slot = new MaskaUtility();
            $payload = [
                'network' => $network->maska,
                'mobile_number' => $data['phone'],
                'airtime_type' => "VTU",
                'Ported_number' => true,
                'amount' => $data['amount'],
            ];
            $api_result = $slot->buyAirtime($payload);
            if(isset($api_result['Status']) && $api_result['Status']== "successful"){
                return $res = [
                    'name' => "glad",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                   'response' => $api_result,
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "glad",
                    'api_status' => "fail",
                    'response' => $api_result['error'] ?? "",
                ];
            }
        }
        // vtung
        else if($network->id == 1 && api_setting('mtn_airtime') == "vtu" || $network->id == 2 && api_setting('glo_airtime') == "vtu" || $network->id == 3 && api_setting('airtel_airtime') == "vtu" || $network->id == 4 && api_setting('mob_airtime') == "vtu" ) {
            $slot = new VtuUtility();
            $payload = [
                'network_id' => $network->vtu,
                'phone' => $data['phone'],
                'amount' => $data['amount'],
            ];
            $api_result = $slot->buyAirtime($payload);
            if(isset($api_result['code']) && $api_result['code']== "success"){
                return $res = [
                    'name' => "vtu",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                   'response' => $api_result,
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "vtu",
                    'api_status' => "fail",
                    'response' => $api_result['error'] ?? "",
                ];
            }
        }
        // flutterwave
        else if($network->id == 1 && api_setting('mtn_airtime') == "flutterwave" || $network->id == 2 && api_setting('glo_airtime') == "flutterwave" || $network->id == 3 && api_setting('airtel_airtime') == "flutterwave" || $network->id == 4 && api_setting('mob_airtime') == "flutterwave" ) {
            $slot = new FlutterUtility();
            $payload = [
                'reference' => $data['ref'],
                'customer' => '+234' . ltrim($data['phone'], '0'),
                'amount' => $data['amount'],
                "recurrence" =>  "ONCE",
                "type" => "AIRTIME",
                "country"  =>  "NG",
            ];
            $api_result = $slot->createBill($payload);
            if(isset($api_result['status']) && $api_result['status']== "success"){
                return $res = [
                    'name' => "flutterwave",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                   'response' => $api_result,
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "flutterwave",
                    'api_status' => "fail",
                    'response' => $api_result ?? "",
                ];
            }
        }
        // sochitel
        else if($network->id == 1 && api_setting('mtn_airtime') == "sochi" || $network->id == 2 && api_setting('glo_airtime') == "sochi" || $network->id == 3 && api_setting('airtel_airtime') == "sochi" || $network->id == 4 && api_setting('mob_airtime') == "sochi" ) {
            $slot = new SochiUtility();
            $comm = "execTransaction";
            $payload = [
                // 'userReference' => $data['ref'],
                'msisdn' => '234' . ltrim($data['phone'], '0'),
                'amount' => $data['amount'],
                "operator" => $network->sochi,
                "simulate" => 0,
                // "productId" => 1,

            ];
            $api_result = $slot->makeRequest($comm, $payload);
            if(isset($api_result['status']) && ($api_result['status']['typeName']== "Success" || $api_result['status']['typeName']== "Pending")){

                return $res = [
                    'name' => "sochitel",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                   'response' => $api_result['result'] ?? $api_result,
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "sochitel",
                    'api_status' => "fail",
                    'response' => $api_result ?? "",
                ];
            }
        }
        return $res =[
            'api_status' => "error",
            'response' => "Something is not right",
            'name' => "none",
            'api_status' => "fail",
            'ref' => null,
        ];
        return $data;
    }
    function purchase_data($data){

        $network = Network::find($data['network']);
        $plan = DataBundle::find($data['plan']);
        // glad
        if($network->id == 1 && api_setting('mtn_data') == "glad" || $network->id == 2 && api_setting('glo_data') == "glad" || $network->id == 3 && api_setting('airtel_data') == "glad" || $network->id == 4 && api_setting('mob_data') == "glad" ) {
            $slot = new GladApi();
            $payload = [
                'network' => ($network->glad),
                'mobile_number' => $data['phone'],
                'Ported_number' => true,
                'plan' => $plan->glad,
            ];

            $api_result = $slot->buyData($payload);
            if(isset($api_result['Status']) && $api_result['Status']== "successful"){
                return $res = [
                    'name' => "glad",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'message' => $api_result['api_response'],
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "glad",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        // 3tdata
        else if($network->id == 1 && api_setting('mtn_data') == "n3tdata" || $network->id == 2 && api_setting('glo_data') == "n3tdata" || $network->id == 3 && api_setting('airtel_data') == "n3tdata" || $network->id == 4 && api_setting('mob_data') == "n3tdata") {
            $slot = new N3tdataUtility();
            $payload = [
                'network' => $network->n3tdata,
                'phone' => $data['phone'],
                'data_plan' => $plan->n3tdata,
                'bypass' => true,
                'request-id' => $data['ref'],
            ];
            $api_result = $slot->buyData($payload);
            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'name' => "n3tdata",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'message' => $api_result['message'],
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "glad",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        // maska
        else if($network->id == 1 && api_setting('mtn_data') == "maska" || $network->id == 2 && api_setting('glo_data') == "maska" || $network->id == 3 && api_setting('airtel_data') == "maska" || $network->id == 4 && api_setting('mob_data') == "maska") {
            $slot = new MaskaUtility();
            $payload = [
                'network' => ($network->maska),
                'mobile_number' => $data['phone'],
                'Ported_number' => true,
                'plan' => $plan->maska,
            ];
            $api_result = $slot->buyData($payload);
            if(isset($api_result['Status']) && $api_result['Status']== "successful"){
                return $res = [
                    'name' => "maska",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'message' => $api_result['api_response'],
                   'response' => $api_result,
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "maska",
                    'api_status' => "fail",
                    'response' => $api_result['error'] ?? "",
                ];
            }
        }
        // legitway
        else if($network->id == 1 && api_setting('mtn_data') == "legit" || $network->id == 2 && api_setting('glo_data') == "legit" || $network->id == 3 && api_setting('airtel_data') == "legit" || $network->id == 4 && api_setting('mob_data') == "legit") {
            $slot = new LegitwayUtility();
            $payload = [
                'network' => $network->legit,
                'phone' => $data['phone'],
                'data_plan' => $plan->legit,
                'bypass' => true,
                'request-id' => $data['ref'],
            ];
            $api_result = $slot->buyData($payload);
            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'name' => "legit",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'message' => $api_result['message'],
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "legit",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        // vtung
        else if($network->id == 1 && api_setting('mtn_data') == "vtu" || $network->id == 2 && api_setting('glo_data') == "vtu" || $network->id == 3 && api_setting('airtel_data') == "vtu" || $network->id == 4 && api_setting('mob_data') == "vtu") {
            $slot = new VtuUtility();
            $payload = [
                'network_id' => $network->legit,
                'phone' => $data['phone'],
                'variation_id' => $plan->vtu,
            ];
            $api_result = $slot->buyData($payload);
            if(isset($api_result['code']) && $api_result['code'] == "success"){
                return $res = [
                    'name' => "vrung",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'message' => $api_result['message'],
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "vtung",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        // flutterwave
        else if($network->id == 1 && api_setting('mtn_data') == "flutterwave" || $network->id == 2 && api_setting('glo_data') == "flutterwave" || $network->id == 3 && api_setting('airtel_data') == "flutterwave" || $network->id == 4 && api_setting('mob_data') == "flutterwave" || $network->id == 7 && api_setting('spectranet_data') == "flutterwave"|| $network->id == 6 && api_setting('smile_data') == "flutterwave") {
            $slot = new FlutterUtility();
            $url = "billers/{$plan['flutter1']}/items/{$plan['flutter2']}/payment";

            $payload = [
                "country" => "NG",
                "customer_id" => $data['phone'],
                'amount' => $plan['flutter3'],
                'reference' => $data['ref']
            ];

            $api_result = $slot->payBill($payload, $url);
            if(isset($api_result['status']) && $api_result['status']== "success"){
                return $res = [
                    'name' => "flutterwave",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'message' => $api_result['message'] ?? "{$plan->name} purchase was successful",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "flutterwave",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        // sochi
        else if($network->id == 1 && api_setting('mtn_data') == "sochi" || $network->id == 2 && api_setting('glo_data') == "sochi" || $network->id == 3 && api_setting('airtel_data') == "sochi" || $network->id == 4 && api_setting('mob_data') == "sochi" || $network->id == 7 && api_setting('spectranet_data') == "sochi"|| $network->id == 6 && api_setting('smile_data') == "sochi") {
            $slot = new SochiUtility();
            $comm = "execTransaction";
            $payload = [
                // 'userReference' => $data['ref'],
                "operator" => $network->sochi,
                "simulate" => 0,
                'msisdn' => '234' . ltrim($data['phone'], '0') ,
                'amount' => $plan->sochi_amount,
                "productId" => $plan->sochi,
            ];

            $api_result = $slot->makeRequest($comm, $payload);
            if(isset($api_result['status']) && ($api_result['status']['typeName']== "Success" || $api_result['status']['typeName']== "Pending")){

                return $res = [
                    'name' => "sochitel",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'message' => "{$plan->name} purchase was successful",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "sochitel",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        return $res =[
            'api_status' => "error",
            'response' => "Something is not right",
            'name' => "none",
            'api_status' => "fail",
            'ref' => null,
        ];
        return $data;
    }
    // xcavle
    function purchase_cabletv($data){

        $decoder = Decoder::find($data['decoder']);
        $plan = CablePlan::find($data['plan']);
        // glad
        if(api_setting('cable_api') == "glad") {
            $slot = new GladApi();
            $payload = [
                'cablename' => ($decoder->id),
                'smart_card_number' => $data['customer'],
                'cableplan' => $plan->glad,
                'customer_name' => $data['name']
            ];

            $api_result = $slot->buyCablesub($payload);
            if(isset($api_result['Status']) && ($api_result['Status']== "processing" || $api_result['Status']== "successful")){
                return $res = [
                    'name' => "glad",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "glad",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        // 3tdata
        else if(api_setting('cable_api') == "n3tdata") {
            $slot = new N3tdataUtility();
            $payload = [
                'cable' => ($decoder->id),
                'iuc' => $data['customer'],
                'cable_plan' => $plan->n3tdata,
                'request-id' => $data['ref'],
                'bypass' => true
            ];
            $api_result = $slot->buyCablesub($payload);
            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'name' => "n3tdata",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'message' => $api_result['message'],
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "n3tdata",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        else if(api_setting('cable_api') == "legit") {
            $slot = new LegitwayUtility();
            $payload = [
                'cable' => ($decoder->id),
                'iuc' => $data['customer'],
                'cable_plan' => $plan->legit,
                'request-id' => $data['ref'],
                'bypass' => true
            ];
            $api_result = $slot->buyCablesub($payload);
            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'name' => "legit",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'message' => $api_result['message'],
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "legit",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        else if(api_setting('cable_api') == "vtu") {
            $slot = new VtuUtility();
            $payload = [
                'service_id' => ($decoder->vtu),
                'smartcard_number' => $data['customer'],
                'variation_id' => $plan->vtu,
                'phone' => auth()->user()->phone ?? "08108565816",
            ];
            $api_result = $slot->buyCablesub($payload);
            if(isset($api_result['code']) && $api_result['code'] == "success"){
                return $res = [
                    'name' => "vtu",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'message' => $api_result['message'],
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "vtu",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        // maska
        else if(api_setting('cable_api') == "maska") {
            $slot = new MaskaUtility();
            $payload = [
                'cablename' => ($decoder->id),
                'smart_card_number' => $data['customer'],
                'cableplan' => $plan->maska,
                'customer_name' => $data['name']
            ];
            $api_result = $slot->buyCablesub($payload);
            if(isset($api_result['Status']) && ($api_result['Status']== "processing" || $api_result['Status']== "successful")){
                return $res = [
                    'name' => "maska",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "maska",
                    'api_status' => "fail",
                    'response' => $api_result['error'] ?? "",
                ];
            }
        }
        // Flutterwave
        else if(api_setting('cable_api') == "flutterwave") {
            $slot = new FlutterUtility();
            $url = "billers/{$decoder->flutter}/items/{$plan->flutter2}/payment";
            $payload = [
                "country" => "NG",
                "customer_id" => $data['customer'],
                'amount' => $plan['price'],
                'reference' => $data['ref'],
            ];
            $api_result = $slot->payBill($payload, $url);
            if(isset($api_result['status']) && ($api_result['status']== "success" || $api_result['status']== "successful")){
                return $res = [
                    'name' => "flutterwave",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "flutterwave",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        // sochitel
        else if(api_setting('cable_api') == "sochi") {
            $slot = new SochiUtility();
            $comm = "execTransaction";
            $payload = [
                // 'userReference' => $data['ref'],
                "operator" => $decoder->sochi,
                "simulate" => 0,
                "productId" => $plan->sochi,
                'accountId' => $data['customer'],
                'amount' => $plan->sochi_amount,
                "simulate" => 0,
            ];

            $api_result = $slot->makeRequest($comm, $payload);
            if(isset($api_result['status']) && ($api_result['status']['typeName']== "Success" || $api_result['status']['typeName']== "Pending")){

                return $res = [
                    'name' => "sochitel",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                   'response' => $api_result['result'] ?? $api_result,
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "sochitel",
                    'api_status' => "fail",
                    'response' => $api_result ?? "",
                ];
            }
        }
        return $res =[
            'api_status' => "error",
            'response' => "Something is not right",
            'name' => "none",
            'api_status' => "fail",
            'ref' => null,
        ];
        return $data;
    }
    // exam
    function purchase_exam($data){

        $plan = Education::find($data['exam']);
        // glad
        if(api_setting('exam_api') == "glad") {
            $slot = new GladApi();

            $payload = [
                'exam_name' => strtoupper($plan['glad']),
                'quantity' => $data['quantity'],
            ];

            $api_result = $slot->buyExampins($payload);

            $logFile = 'logs/exam_webhook.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);
            if(isset($api_result['Status']) && $api_result['Status']== "successful"){
                $pinsArray = json_decode($api_result['pins'], true);
                return $res = [
                    'name' => "glad",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'pin' => $pinsArray['pin'] ?? $api_result['data']['pin'] ?? $api_result['pin'] ?? "",
                    'serial' => ""
                ];

            }else{
                return $res = [
                    'name' => "glad",
                    'ref' => $data['ref'],
                    'api_status' => "fail",
                    'response' => $api_result,
                    'pin' => null,
                    'serial' => null
                ];
            }
        }
        // 3tdata
        else if(api_setting('exam_api') == "n3tdata") {
            $slot = new N3tdataUtility();
            $payload = [
                'exam' => $plan['n3tdata'],
                'quantity' => $data['quantity'],
            ];
            $api_result = $slot->buyExampins($payload);

            $logFile = 'logs/exam_webhook.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);

            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'name' => "maska",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'pin' => $api_result['pin'] ?? $api_result['pins'] ?? "",
                    'serial' => $api_result['serial'] ?? "",
                    'response' => $api_result,
                ];
            }else{
                 return $res = [
                    'name' => "vend1",
                    'ref' => $data['ref'],
                    'api_status' => "fail",
                    'response' => $api_result,
                    'pin' => null,
                    'serial' => null
                ];
            }
        }
        // legitway
        else if(api_setting('exam_api') == "legit") {
            $slot = new LegitwayUtility();
            $payload = [
                'exam' => $plan['legit'],
                'quantity' => $data['quantity'],
            ];
            $api_result = $slot->buyExampins($payload);

            $logFile = 'logs/exam_webhook.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);
            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'name' => "legit",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'pin' => $api_result['pin'] ?? $api_result['pins']??"",
                    'serial' => $api_result['serial'] ?? "",
                    'response' => $api_result,
                ];
            }else{
                 return $res = [
                    'name' => "legit",
                    'ref' => $data['ref'],
                    'api_status' => "fail",
                    'response' => $api_result,
                    'pin' => null,
                    'serial' => null
                ];
            }
        }
        // maska
        else if(api_setting('exam_api') == "maska") {
            $slot = new MaskaUtility();
            $payload = [
                'exam_name' => strtoupper($plan['maska']),
                'quantity' => $data['quantity'],
            ];
            $api_result = $slot->buyExampins($payload);

            $logFile = 'logs/exam_webhook.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);

            if(isset($api_result['Status']) && $api_result['Status']== "successful"){
                $pinsArray = json_decode($api_result['pins'], true);
                return $res = [
                    'name' => "maska",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'pin' => $pinsArray['pin'] ?? $api_result['data']['pin'] ?? $api_result['pin'] ?? "",
                    'serial' => "",
                    'response' => $api_result,
                ];
            }else{
                 return $res = [
                    'name' => "maska",
                    'ref' => $data['ref'],
                    'api_status' => "fail",
                    'response' => $api_result,
                    'pin' => null,
                    'serial' => null
                ];
            }
        }
        //vtung
        else if(api_setting('exam_api') == "vtu") {
            $slot = new VtuUtility();
            $payload = [
                'exam' => $plan['legit'],
                'quantity' => $data['quantity'],
            ];
            $api_result = $slot->buyExampins($payload);

            $logFile = 'logs/exam_webhook.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);
            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'name' => "legit",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'pin' => $api_result['pin'] ?? $api_result['pins']??"",
                    'serial' => $api_result['serial'] ?? "",
                    'response' => $api_result,
                ];
            }else{
                 return $res = [
                    'name' => "legit",
                    'ref' => $data['ref'],
                    'api_status' => "fail",
                    'response' => $api_result,
                    'pin' => null,
                    'serial' => null
                ];
            }
        }
        return $res =[
            'api_status' => "error",
            'response' => "Something is not right",
            'name' => "none",
            'api_status' => "fail",
            'ref' => null,
        ];
        return $data;
    }
    // power
    function purchase_power($data){

        $plan = Electricity::find($data['disco']);
        // glad
        if(api_setting('power_api') == "glad") {
            $slot = new GladApi();

            if($data['type'] == '1'){
                $data['type'] = "Prepaid";
            }else{
                $data['type'] = "Postpaid";
            }

            $payload = [
                'meter_number' => $data['number'],
                'disco_name' => $plan->glad,
                'Customer_Phone' => $data['phone'] ?? "090123456789",
                'customer_name' => $data['name'] ?? "Bypass User",
                'customer_address' => "Test Address ",
                'amount' => $data['amount'],
                'MeterType' => $data['type'],
            ];

            $api_result = $slot->buyPower($payload);

            $logFile = 'logs/powertrx.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);

            if(isset($api_result['Status']) && $api_result['Status']== "successful"){
                return $res = [
                    'name' => "glad",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'token' => $api_result['token'] ?? " ",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "glad",
                    'api_status' => "fail",
                    'response' => $api_result,
                    "token" => "null",
                ];
            }
        }
        // 3tdata
        else if(api_setting('power_api') == "n3tdata") {
            $slot = new N3tdataUtility();
            if($data['type'] == '1'){
                $data['type'] = 'prepaid';
            }else{
                $data['type'] = 'postpaid';
            }
            $payload = [
                'bypass' => false,
                'request-id' => $data['ref'],
                'meter_number' => $data['number'],
                'disco' => $plan->n3tdata,
                'amount' => $data['amount'],
                'meter_type' => $data['type'],
            ];

            $api_result = $slot->buyPower($payload);

            $logFile = 'logs/powertrx.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);

            if(isset($api_result['status']) && $api_result['status'] == "success"){

                return $res = [
                    'name' => "n3tdata",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'token' => $api_result['token'] ?? "",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "n3tdata",
                    'api_status' => "fail",
                    'response' => $api_result,
                    "token" => "null",
                ];
            }
        }
        else if(api_setting('power_api') == "legit") {
            $slot = new LegitwayUtility();
            if($data['type'] == '1'){
                $data['type'] = 'prepaid';
            }else{
                $data['type'] = 'postpaid';
            }
            $payload = [
                'bypass' => true,
                'request-id' => $data['ref'],
                'meter_number' => $data['number'],
                'disco' => $plan->legit,
                'amount' => $data['amount'],
                'meter_type' => $data['type'],
            ];

            $api_result = $slot->buyPower($payload);

            $logFile = 'logs/powertrx.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);

            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'name' => "legit",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'token' => $api_result['token'] ?? "",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "legit",
                    'api_status' => "fail",
                    'response' => $api_result,
                    "token" => "null",
                ];
            }
        }
        // maska
        else if(api_setting('power_api') == "maska") {
            $slot = new MaskaUtility();

            if($data['type'] == '1'){
                $data['type'] = 1;
            }else{
                $data['type'] = 2;
            }

            $payload = [
                'meter_number' => $data['number'],
                'disco_name' => $plan->maska,
                'Customer_Phone' => "1234432131",
                'customer_name' => $data['name'],
                'customer_address' => " ",
                'amount' => $data['amount'],
                'MeterType' => $data['type'],
            ];

            $api_result = $slot->buyPower($payload);

            $logFile = 'logs/powertrx.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);

            if(isset($api_result['Status']) && $api_result['Status']== "successful"){
                return $res = [
                    'name' => "maska",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'token' => $api_result['token'] ?? "",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "maska",
                    'api_status' => "fail",
                    'response' => $api_result,
                    "token" => "null",
                ];
            }
        }
        //vtung
        else if(api_setting('power_api') == "vtu") {
            $slot = new VtuUtility();
            if($data['type'] == '1'){
                $data['type'] = 'prepaid';
            }else{
                $data['type'] = 'postpaid';
            }
            $payload = [
                'phone' => auth()->user->phone ?? "08108585216",
                'meter_number' => $data['number'],
                'service_id' => $plan->vtu,
                'amount' => $data['amount'],
                'variation_id' => $data['type'],
            ];

            $api_result = $slot->buyPower($payload);

            $logFile = 'logs/powertrx.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);

            if(isset($api_result['code']) && $api_result['code'] == "success"){
                return $res = [
                    'name' => "vtu",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'token' => $api_result['token'] ?? $api_result['data']['token'] ?? "",
                    'units' => $api_result['units'] ?? $api_result['data']['units'] ??  "",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "vtu",
                    'api_status' => "fail",
                    'response' => $api_result,
                    "token" => "null",
                ];
            }
        }
        // flutterwave
        else if(api_setting('power_api') == "flutterwave") {
            $slot = new FlutterUtility();
            $url = "billers/{$plan->flutter}/items/{$plan->flutter2}/payment";

            $payload = [
                "country" => "NG",
                "customer_id" => $data['number'],
                'amount' => $data['amount'],
                'reference' => $data['ref']
            ];

            $api_result = $slot->payBill($payload, $url);

            $logFile = 'logs/powertrx.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);

            if(isset($api_result['status']) && $api_result['status']== "success"){
                return $res = [
                    'name' => "flutterwave",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'token' => $api_result['data']['recharge_token'] ?? "",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "flutterwave",
                    'api_status' => "fail",
                    'response' => $api_result,
                    "token" => "null",
                ];
            }
        }
        // Sochitel
        else if(api_setting('power_api') == "sochi") {
            $slot = new SochiUtility();
            $comm = "execTransaction";
            $payload = [
                // 'userReference' => $data['ref'],
                "operator" => $plan->sochi,
                // 'msisdn' => $data['number'],
                'amount' => $data['amount'],
                "simulate" => 0,
                "productId" => $plan->sochi_product,
                'accountId' => $data['number']

            ];
            $api_result = $slot->makeRequest($comm, $payload);
            if(isset($api_result['status']) && ($api_result['status']['typeName']== "Success" || $api_result['status']['typeName']== "Pending")){
                return $res = [
                    'name' => "sochitel",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result['result'] ?? $api_result,
                    'token' => $api_result['result']['pin']['number'] ?? $api_result['result']['pin'] ??  " ",

                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "sochitel",
                    'api_status' => "fail",
                    'response' => $api_result ?? "",
                ];
            }
        }
        return $res =[
            'api_status' => "error",
            'response' => "Something is not right",
            'name' => "none",
            'api_status' => "fail",
            'ref' => null,
        ];
        return $data;
    }
    // Datacard
    function purchase_datacard($data){
        $plan = DatacardPlan::find($data['plan_id']);
        $network =Network::find($data['network']);
        if($network->id == 1 && api_setting('mtn_datacard') == "glad" || $network->id == 2 && api_setting('glo_datacard') == "glad" || $network->id == 3 && api_setting('airtel_datacard') == "glad" || $network->id == 4 && api_setting('mob_datacard') == "glad") {
            $slot = new GladApi();

            $payload = [
                'network' => $network['glad'],
                'data_plan' => $plan['glad'],
                'quantity' => $data['quantity'],
                'name_on_card'  => $data['name']
            ];

            $api_result = $slot->buyDatacard($payload);
            if(isset($api_result['Status']) && $api_result['Status']== "successful"){
                return $res = [
                    'name' => "glad",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'pin' => $api_result['pin'],
                    "serial" => $api_result['serial'] ?? "",
                    "load_pin" => $api_result['load_pin'],
                    'check_balance' =>  $api_result["check_balance"] ?? "",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "glad",
                    'api_status' => "fail",
                    'response' => $api_result,
                    "token" => "null",
                ];
            }
        }
        else if($network->id == 1 && api_setting('mtn_datacard') == "n3tdata" || $network->id == 2 && api_setting('glo_datacard') == "n3tdata" || $network->id == 3 && api_setting('airtel_datacard') == "n3tdata" || $network->id == 4 && api_setting('mob_datacard') == "n3tdata") {
            $slot = new N3tdataUtility();

            $payload = [
                'network' => $network['n3tdata'],
                'plan_type' => $plan['n3tdata'],
                'quantity' => $data['quantity'],
                'card_name'  => $data['name']
            ];

            $api_result = $slot->buyDatacard($payload);
            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'pin' => $api_result['pin'],
                    "serial" => $api_result['serial'],
                    "load_pin" => $api_result['load_pin'],
                    'name' => "n3tdata",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'check_balance' =>  $api_result["check_balance"] ?? "",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "maska",
                    'api_status' => "fail",
                    'response' => $api_result,
                    "token" => "null",
                ];
            }
        }
        else if($network->id == 1 && api_setting('mtn_datacard') == "legit" || $network->id == 2 && api_setting('glo_datacard') == "legit" || $network->id == 3 && api_setting('airtel_datacard') == "legit" || $network->id == 4 && api_setting('mob_datacard') == "legit") {
            $slot = new LegitwayUtility();

            $payload = [
                'network' => $network['legit'],
                'plan_type' => $plan['legit'],
                'quantity' => $data['quantity'],
                'card_name'  => $data['name']
            ];

            $api_result = $slot->buyDatacard($payload);
            if(isset($api_result['status']) && $api_result['status'] == "success"){
                return $res = [
                    'pin' => $api_result['pin'],
                    "serial" => $api_result['serial'],
                    "load_pin" => $api_result['load_pin'],
                    'name' => "legit",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'check_balance' =>  $api_result["check_balance"],
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "legit",
                    'api_status' => "fail",
                    'response' => $api_result,
                    "token" => "null",
                ];
            }
        }
        else if($network->id == 1 && api_setting('mtn_datacard') == "maska" || $network->id == 2 && api_setting('glo_datacard') == "maska" || $network->id == 3 && api_setting('airtel_datacard') == "maska" || $network->id == 4 && api_setting('mob_datacard') == "maska") {
            $slot = new MaskaUtility();

            $payload = [
                'network' => $network['maska'],
                'data_plan' => $plan['maska'],
                'quantity' => $data['quantity'],
                'name_on_card'  => $data['name']
            ];

            $api_result = $slot->buyDatacard($payload);
            if(isset($api_result['Status']) && $api_result['Status']== "successful"){
                return $res = [
                    'name' => "maska",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                    'pin' => $api_result['pin'],
                    "serial" => $api_result['serial'],
                    "load_pin" => $api_result['load_pin'],
                    'check_balance' =>  $api_result["check_balance"] ?? "",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "maska",
                    'api_status' => "fail",
                    'response' => $api_result,
                    "token" => "null",
                ];
            }
        }
        return $res =[
            'api_status' => "error",
            'response' => "Something is not right",
            'name' => "none",
            'api_status' => "fail",
            'ref' => null,
        ];
    }

    // Bulksms
    function send_bulksms($data){
        if(api_setting('bulksms') == 'n3tdata'){
            $vend = new N3tdataUtility();
            $payload = [
                "sender" =>$data['sender'],
                "number" => $data['number'],
                "message" => $data['message'],
            ];
            $api_result = $vend->sendSMS($payload);
            if (isset($api_result) && $api_result['status'] == 'success' )  {
                return $res =[
                    'api_status' => "success",
                    'response' => $api_result,
                    'name' => "n3tdata",
                    'ref' => $data['ref'],
                ];
            } else {
                return $res =[
                    'api_status' => "error",
                    'response' => $api_result,
                    'name' => "n3tdata",
                    'ref' => $data['ref'],
                ];
            }

        }
        elseif(api_setting('bulksms') == 'legit'){
            $vend = new LegitwayUtility();
            $payload = [
                "sender" =>$data['sender'],
                "number" => $data['number'],
                "message" => $data['message'],
            ];
            $api_result = $vend->sendSMS($payload);
            if (isset($api_result) && $api_result['status'] == 'success' )  {
                return $res =[
                    'api_status' => "success",
                    'response' => $api_result,
                    'name' => "legit",
                    'ref' => $data['ref'],
                ];
            } else {
                return $res =[
                    'api_status' => "error",
                    'response' => $api_result,
                    'name' => "legit",
                    'ref' => $data['ref'],
                ];
            }

        }
        elseif(api_setting('bulksms') == 'bulksmsng'){
            $api = new Bulksmsng();
            $payload = [
                'from' => $data['sender'],
                'body' => $data['message'],
                'to' => $data['number'],
                "gateway" => "direct-refund",
                'customer_reference' => $data['ref'],
            ];

            $api_result = $api->sendSms($payload);
            if(isset($api_result['data']['status']) && $api_result['data']['status'] == "success"){
                return $res =[
                    'api_status' => "success",
                    'response' => $api_result,
                    'name' => "bulksmsng",
                    'ref' => $data['ref'],
                ];
            }else{
                return $res =[
                    'api_status' => "error",
                    'response' => $api_result,
                    'name' => "bulksmsng",
                    'ref' => $data['ref'],
                ];
            }

        }
        return $res =[
            'api_status' => "error",
            'response' => "Something is not right",
            'name' => "none",
            'api_status' => "fail",
            'ref' => $data['ref'],
        ];
    }

    // betting
    function purchase_bet($data){

        $plan = Betsite::findOrFail($data['betsite']);
        if(api_setting('bet_api') == 'opay'){
            $vend = new OpayUtility();

            $payload = [
                "serviceType" => 'betting',
                "amount"=> $data['amount'] *100,
                "customerId" => $data["number"],
                "provider" => $data['service'],
                "reference" => $data["ref"],
                "country"=> "NG",
                "currency"=> "NGN",
            ];

            $api_result = $vend->purchaseBet($payload);
            if (isset($api_result) && $api_result['success'] == true && $api_result['message'] == "SUCCESSFUL" )  {
                return $res =[
                    'api_status' => "success",
                    'response' => $api_result,
                    'name' => "opay",
                    'ref' => $data['ref'],
                ];
            } else {
                return $res =[
                    'api_status' => "error",
                    'response' => $api_result,
                    'name' => "opay",
                    'ref' => $data['ref'],
                ];
            }
        }
        else if(api_setting('bet_api') == 'sochi'){
            $vend = new SochiUtility();
            $comm = "execTransaction";

            $payload = [
                // 'userReference' => $data['ref'],
                "operator" => $plan['sochi_operator'],
                'productId' => $plan['sochi_code'],
                'accountId' => $data['number'],
                'amount' => $data['amount'],
            ];

            $api_result = $vend->makeRequest($comm, $payload);
            if(isset($api_result['status']) && ($api_result['status']['typeName']== "Success" || $api_result['status']['typeName']== "Pending")){
                return $res =[
                    'api_status' => "success",
                    'response' => $api_result,
                    'name' => "sochi",
                    'ref' => $data['ref'],
                ];
            } else {
                return $res =[
                    'api_status' => "error",
                    'response' => $api_result,
                    'name' => "sochi",
                    'ref' => $data['ref'],
                ];
            }
        }
        return $res =[
            'api_status' => "error",
            'response' => "Something is not right",
            'name' => "none",
            'api_status' => "fail",
            'ref' => $data['ref'],
        ];
    }

    // utility payment
    function purchase_utility($data){
        // flutterwave
        if("flutterwave" == "flutterwave") {
            $slot = new FlutterUtility();

            $url = "billers/{$data['biller_code']}/items/{$data['item_code']}/payment";

            $payload = [
                "country" => "NG",
                "customer_id" => $data['number'],
                'amount' => $data['amount'],
                'reference' => $data['ref']
            ];

            $api_result = $slot->payBill($payload, $url);

            $logFile = 'logs/utilitypay.txt';
            $logMessage = json_encode($api_result, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);

            if(isset($api_result['status']) && $api_result['status']== "success"){
                return $res = [
                    'name' => "flutterwave",
                    'ref' => $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result,
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "flutterwave",
                    'api_status' => "fail",
                    'response' => $api_result,
                ];
            }
        }
        return $res =[
            'api_status' => "error",
            'response' => "Unable to process Payment",
            'name' => "none",
            'api_status' => "fail",
            'ref' => null,
        ];
    }

    // intl topup
    function processTopup($data){
        // Sochitel
        if("sochi" == "sochi") {
            $slot = new SochiUtility();
            $comm = "execTransaction";

            $payload = [
                // 'userReference' => $data['ref'],
                "operator" => $data['operator'],
                'msisdn' => $data['number'],
                'amount' => $data['amount'],
            ];
            $api_result = $slot->makeRequest($comm, $payload);
            if(isset($api_result['status']) && ($api_result['status']['typeName']== "Success" || $api_result['status']['typeName']== "Pending")){
                return $res = [
                    'name' => "sochitel",
                    'ref' => $api_result['reference'] ?? $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result['result'] ?? $api_result,
                    'price' => $api_result['result']['amount']['operator'] ?? $data['amount'],
                    'price_symbol' => $api_result['result']['currency']['operator'] ?? "NGN",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "sochitel",
                    'api_status' => "fail",
                    'response' => $api_result ?? "",
                ];
            }
        }
    }
    // Giftcard

    function processGiftcard($data){
        // Sochitel
        if("sochi" == "sochi") {
            $slot = new SochiUtility();
            $comm = "execTransaction";

            $payload = [
                // 'userReference' => $data['ref'],
                "operator" => $data['operator'],
                'productId' => $data['product'],
                'amount' => $data['amount'],
            ];
            $api_result = $slot->makeRequest($comm, $payload);
            if(isset($api_result['status']) && ($api_result['status']['typeName']== "Success" || $api_result['status']['typeName']== "Pending")){
                return $res = [
                    'name' => "sochitel",
                    'ref' => $api_result['reference'] ?? $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result['result'] ?? $api_result,
                    'pin' => $api_result['result']['pin'] ?? "",
                    'meta' => $api_result['result']['pin'] ?? "",
                    'price' => $api_result['result']['amount']['operator'] ?? $data['amount'],
                    'price_symbol' => $api_result['result']['currency']['operator'] ?? "NGN",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "sochitel",
                    'api_status' => "fail",
                    'response' => $api_result ?? "",
                ];
            }
        }
    }

    function processGlobal($data){
        $plan = BillProduct::find($data['plan']);
        $operator = Operator::find($data['operator']);
        if("sochi" == "sochi") {
            $slot = new SochiUtility();
            $comm = "execTransaction";

            $payload = [
                // 'userReference' => $data['ref'],
                "operator" => $operator['code'],
                'productId' => $plan['code'],
                'msisdn' => $data['number'],
                'amount' => $data['amount'],
            ];

            $api_result = $slot->makeRequest($comm, $payload);
            if(isset($api_result['status']) && ($api_result['status']['typeName']== "Success" || $api_result['status']['typeName']== "Pending")){
                return $res = [
                    'name' => "sochitel",
                    'ref' => $api_result['reference'] ?? $data['ref'],
                    'api_status' => "success",
                    'response' => $api_result['result'] ?? $api_result,
                    'pin' => $api_result['result']['pin'] ?? "",
                    'meta' => $api_result['result']['pin'] ?? "",
                    'price' => $api_result['result']['amount']['operator'] ?? $data['amount'],
                    'price_symbol' => $api_result['result']['currency']['operator'] ?? "NGN",
                ];
            }else{
                return $res = [
                    'ref' => $data['ref'],
                    'name' => "sochitel",
                    'api_status' => "fail",
                    'response' => $api_result ?? "",
                ];
            }
        }
    }
}
