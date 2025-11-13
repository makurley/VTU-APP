<?php

namespace App\Utility;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;

class GladApi

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
    public function getUser(){
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/user/')->json();
        return $response;
    }

    public function buyAirtime($data)
    {

        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/topup/', $data)->json();

        return $response;

    }
    // buy data
    public function buyData($data)
    {
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/data/', $data)->json();

        return $response;

    }
    // cable sub
    public function buyCablesub($data)
    {
        // $formdata = [
        //     'amount' => $data['amount'],
        //     'cablename' => $data['service'],
        //     'cableplan' => $data['plan'],
        //     'smart_card_number' => $data['customer']
        // ];

        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/cablesub/', $data)->json();

        return $response;

    }
    public function validateCable($data)
    {
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->get('https://www.gladtidingsdata.com//ajax/validate_iuc/', $data)->json();

        return $response;

    }
    // power
    public function buyPower($data)
    {
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/billpayment/', $data)->json();

        return $response;

    }
    public function validateMeter($data)
    {
        $response = Http::timeout(120)->withHeaders([
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

        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/rechargepin/', $formdata)->json();

        return $response;

    }
    // Buy Datacard
    public function buyDatacard($data)
    {
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/datarechargepin/', $data)->json();

        return $response;

    }
    //generate Pins
    public function buyExamPins($data)
    {

        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/epin/', $data)->json();

        return $response;

    }
}
