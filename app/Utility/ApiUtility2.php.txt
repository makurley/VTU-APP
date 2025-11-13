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
        $this->secretkey = env('GSUBZ_API');
        $this->baseurl = "https://gsubz.com/api";
    }
    
    public function generateReference()
    {
        return 'gbz_' . uniqid(time());
    }
    public function getHeader()
    {
        return 'Bearer ' . $this->secretkey;
    }

    public function buyAirtime($data)
    {
        $formdata = [
            'amount' => $data['amount'],
            'phone' => $data['phone'],
            'serviceID' => $data['code'],
            'api' => $this->secretkey
        ];
       
        $response = Http::withHeaders([
            'api' => 'Bearer '.$this->secretkey,
            'Authorization' => 'Bearer '.$this->secretkey
        ])->asMultipart()->post($this->baseurl.'/pay/', $formdata);

        return $response;
        
    }
    // buy data
    public function buyData($data)
    {
        $formdata = [
            'amount' => $data['amount'],
            'phone' => $data['phone'],
            'serviceID' => $data['service'],
            'plan' => $data['plan'],
            'api' => $this->secretkey
        ];
       
        $response = Http::withHeaders([
            'api' => 'Bearer '.$this->secretkey,
            'Authorization' => 'Bearer '.$this->secretkey
        ])->asMultipart()->post($this->baseurl.'/pay/', $formdata);

        return $response;
        
    }
    // cable sub
    public function buyCablesub($data)
    {
        $formdata = [
            'amount' => $data['amount'],
            'phone' => $data['phone'],
            'serviceID' => $data['service'],
            'plan' => $data['plan'],
            'api' => $this->secretkey,
            'customerID' => $data['customer']
        ];
       
        $response = Http::withHeaders([
            'api' => 'Bearer '.$this->secretkey,
            'Authorization' => 'Bearer '.$this->secretkey
        ])->asMultipart()->post($this->baseurl.'/pay/', $formdata);

        return $response;
        
    }
    // power 
    public function buyPower($data)
    {
        $formdata = [
            'amount' => $data['amount'],
            'phone' => $data['phone'],
            'serviceID' => $data['service'],
            'api' => $this->secretkey,
            'customerID' => $data['customer']
        ];
       
        $response = Http::withHeaders([
            'api' => 'Bearer '.$this->secretkey,
            'Authorization' => 'Bearer '.$this->secretkey
        ])->asMultipart()->post($this->baseurl.'/pay/', $formdata);

        return $response;
        
    }

    //generate Pins
    public function buyGeneratePins($data)
    {
        $formdata = [
            'network' => $data['network'],
            'value' => $data['value'],
            'number' => $data['quantity'],
            'api' => $this->secretkey
        ];
       
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretkey
        ])->asMultipart()->post('https://gsubz.com/apiV2/generate/', $formdata);
        
        return $response;
        
    }
}