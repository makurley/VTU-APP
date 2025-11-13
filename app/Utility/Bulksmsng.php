<?php

namespace App\Utility;

use Illuminate\Support\Facades\Http;

class Bulksmsng

{
    protected $token;
    protected $baseurl ;

    public function __construct()
    {
        $this->baseurl = "https://www.bulksmsnigeria.com/api/v2/sms";
        $this->token = env('BULKSMS_KEY');
    }

    public function generateReference()
    {
        return 'BKN_' . uniqid(time());
    }
    public function getHeader()
    {
        $credentials = $this->token;

        return 'Token ' . $credentials;
    }
    // get balance
    public function getBalance()
    {
        $data["api_token"] = $this->token;
        // return $data;
        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            'Accept' => 'application/json',
        ])->get('https://www.bulksmsnigeria.com/api/v2/balance')->json();

        return $response;
    }

    public function sendSms($data)
    {
        $data["api_token"] = $this->token;
        // return $data;
        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            'Accept' => 'application/json',
        ])->post($this->baseurl, $data)->json();

        return $response;
    }

}
