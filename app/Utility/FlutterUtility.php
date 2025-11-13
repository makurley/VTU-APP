<?php

namespace App\Utility;

use Illuminate\Support\Facades\Http;

class FlutterUtility

{
    protected $token;
    protected $baseurl ;

    public function __construct()
    {
        $this->baseurl = "https://api.flutterwave.com/v3";
        $this->token = env('FLW_SECRET_KEY');
    }

    public function generateReference()
    {
        return 'FLW_' . uniqid(time());
    }
    public function getHeader()
    {
        $credentials = [
            "Authorization" => "Bearer ".$this->token,
            "Content-Type"  => "application/json",
        ];

        return $credentials;
    }
    // validate bolls
    public function validateBills($data, $code)
    {
        // return $data;
        $response = Http::withHeaders([
            $this->getHeader()
        ])->get($this->baseurl.'/bill-items/'.$code.'/validate', $data)->json();

        return $response;
    }
    // buy bills
    public function createBill($data)
    {
        // return $data;
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".$this->token,
            "Content-Type"  => "application/json",
        ])->post($this->baseurl.'/bills', $data)->json();

        return $response;
    }

    public function payBill($data, $url)
    {
        // return $data;
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".$this->token,
            "Content-Type"  => "application/json",
        ])->post($this->baseurl.'/'.$url, $data)->json();

        return $response;
    }
    // get Billers
    public function getBillers($code)
    {
        $data = [
            'country' => 'NG'
        ];
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".$this->token,
            "Content-Type"  => "application/json",
        ])->get($this->baseurl.'/bills/'.$code.'/billers', $data)->json();

        return $response;
    }
    public function getBillerItems($code)
    {
        $data = [
            'country' => 'NG'
        ];
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".$this->token,
            "Content-Type"  => "application/json",
        ])->get($this->baseurl.'/billers/'.$code.'/items', $data)->json();

        return $response;
    }
}
