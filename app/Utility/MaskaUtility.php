<?php

namespace App\Utility;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;

class MaskaUtility

{
    protected $secretkey;
    protected $baseurl ;

    public function __construct()
    {
        $this->secretkey = env('MASKA_API');
        $this->baseurl = "https://www.eccdcsub.com.ng/api";
    }

    public function generateReference()
    {
        return 'msk_' . uniqid(time());
    }
    public function getHeader()
    {
        return 'Token ' . $this->secretkey;
    }
    public function getUser(){
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->get($this->baseurl.'/user/')->json();
        return $response;
    }
    public function buyAirtime($data)
    {
        // return $data;
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
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/cablesub/', $data)->json();

        return $response;

    }
    public function validateCable($data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->get('https://www.maskawasubapi.com//ajax/validate_iuc/', $data)->json();

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
        $response = Http::withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->get('https://www.maskawasubapi.com/ajax/validate_meter_number/', $data)->json();

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

    //generate Pins
    public function buyExamPins($data)
    {
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Token '.$this->secretkey
        ])->post($this->baseurl.'/epin/', $data)->json();

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
}
