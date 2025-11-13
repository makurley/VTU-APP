<?php

namespace App\Utility;

use Illuminate\Support\Facades\Http;

class OpayUtility

{
    protected $apikey ;
    protected $enckey ;
    protected $username;
    protected $baseurl ;

    public function __construct()
    {
        $this->username = env('OPAY_ID');
        $this->apikey = env('OPAY_PUBLIC_KEY');
        $this->enckey = env('OPAY_SECRET_KEY');
        $this->baseurl = "https://cashierapi.opayweb.com/api/v3";
        // $this->baseurl = "http://sandbox-cashierapi.opayweb.com/api/v3";

    }
    function generateSignature($privateKey, $payload) {
        ksort($payload);
        $payloadJson = json_encode($payload);
        return $signature = hash_hmac('sha512', $payloadJson, $privateKey);
    }

    public function getHeader()
    {
        $credentials = [
            "Authorization" => "Bearer ".$this->apikey,
            "MerchantId"    => $this->username,
            "Content-Type"  => "application/json",
        ];

        return $credentials;
    }

    public function verifyBet($data)
    {
        $response = Http::withHeaders($this->getHeader()
        )->post($this->baseurl.'/bills/validate', $data)->json();

        return $response;
    }

    public function purchaseBet($data)
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".$this->apikey,
            "MerchantId"    => $this->username,
            "Content-Type"  => "application/json",
            "Encrtyption"   => $this->generateSignature($this->enckey, $data)
        ])->post($this->baseurl.'/bills/bulk-bills', $data)->json();

        return $response;
    }

}
