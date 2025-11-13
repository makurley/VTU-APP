<?php

namespace App\Utility;

use Illuminate\Support\Facades\Http;

class PayvesselUtility

{
    protected $contractcode;
    protected $businessid ;
    protected $publickey ;
    protected $secretkey;
    protected $baseurl ;

    public function __construct()
    {
        $this->contractcode = env('MONNIFY_CONTRACT');
        $this->businessid = env('PAYVESSEL_ID');
        $this->secretkey = env('PAYVESSEL_SECRET_KEY');
        $this->publickey = env('PAYVESSEL_PUBLIC_KEY');

        $this->baseurl = "https://api.payvessel.com/api";
    }

    public function generateReference()
    {
        return 'pv_' . uniqid(time());
    }
    public function getHeader()
    {
        $header = array(
            'Content-Type: application/json',
            'api-key : '. $this->publickey,
            'api-secret: Bearer '.$this->secretkey,
        );
        return $header;
    }

    public function initializePayment($data)
    {
        $formdata = [
            'amount' => $data['amount'],
            'customerEmail' => $data['email'],
            'customerName' => $data['name'],
            'paymentReference' => $data['reference'],
            'currencyCode' =>$data['currency'] ?? "NGN",
            'paymentDescription' => $data['description'],
            'paymentMethods' => ["CARD"],
            'redirectUrl' => $data->redirectUrl ?? url('monnify/success') ,
            "contractCode" => $this->contractcode,
        ];
        $response = Http::withHeaders([
            'Authorization' => $this->getHeader()
        ])->post($this->baseurl.'/v1/merchant/transactions/init-transaction', $formdata)->json();

        return $response;
    }

    // verify transaction
    public function verifyTransaction($data)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->getHeader()
        ])->get($this->baseurl.'/v1/merchant/transactions/query', $data)->json();

        return $response;
    }

    // Reserve account
    public function reserveAccount($data)
    {
        $formdata = [
            'email' => $data['email'],
            'name' => $data['name'],
            'businessid' => $this->businessid,
            'phoneNumber' =>$data['phone'] ?? "08112342211",
            "bankcode" => ["120001"],
            // "preferredBanks" => ["035","232"]

        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'api-key' => $this->publickey,
            'api-secret' => 'Bearer ' . $this->secretkey
        ])->post($this->baseurl.'/external/request/customerReservedAccount/', $formdata)->json();

        return $response;
    }
}
