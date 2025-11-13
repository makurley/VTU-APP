<?php

namespace App\Utility;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;

class ApiUtility

{
    protected $secretkey;
    protected $baseurl ;

    public function __construct()
    {
        $this->secretkey = env('GLADTIDING_API');
        $this->baseurl = "https://www.gladtidingsdata.com/api";
    }

    public function generateReference()
    {
        return 'gld_' . uniqid(time());
    }
    public function getHeader()
    {
        return 'Token ' . $this->secretkey;
    }

    public function buyAirtime($data)
    {
        $formdata = [
            'amount' => $data['amount'],
            'mobile_number' => $data['phone'],
            'network' => $data['code'],
            "Ported_number" => 'false',
            'airtime_type' => "VTU"
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/topup/', $formdata)->json();

        return $response;

    }
    // buy data
    public function buyData($data)
    {
        $formdata = [
            'amount' => $data['amount'],
            'plan' => $data['plan'],
            'mobile_number' => $data['phone'],
            'network' => $data['code'],
            "Ported_number" => 'false'
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/data/', $formdata)->json();

        return $response;

    }
    // cable sub
    public function buyCablesub($data)
    {
        $formdata = [
            'amount' => $data['amount'],
            'cablename' => $data['service'],
            'cableplan' => $data['plan'],
            'smart_card_number' => $data['customer']
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/cablesub/', $formdata)->json();

        return $response;

    }
    public function validateCable($data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->get('https://www.gladtidingsdata.com//ajax/validate_iuc/', $data)->json();

        return $response;

    }
    // power
    public function buyPower($data)
    {
        $formdata = [
            'amount' => $data['amount'],
            // 'MeterType' => $data['type'],
            'disco_name' => $data['service'],
            'meter_number' => $data['number']
        ];
        if($data['type'] == '1'){
            $formdata['MeterType'] = 1;
        }else{
            $formdata['MeterType'] = 2;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/billpayment/', $formdata)->json();

        return $response;

    }
    public function validateMeter($data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->get('https://www.gladtidingsdata.com/ajax/validate_meter_number/', $data)->json();

        return $response;

    }
    //generate Pins
    public function buyGeneratePins($data)
    {
        $formdata = [
            'network' => $data['network'],
            'network_amount' => $data['value'],
            'quantity' => $data['quantity'],
            'name_on_card' => $data['name']
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/rechargepin/', $formdata)->json();

        return $response;

    }

    //generate Pins
    public function buyExamPins($data)
    {
        $formdata = [
            'quantity' => $data['quantity'],
            'exam_name' => $data['name']
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/epin/', $formdata)->json();

        return $response;

    }
}
