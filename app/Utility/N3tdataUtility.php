<?php

namespace App\Utility;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;

class N3tdataUtility

{
    protected $password;
    protected $username;
    protected $baseurl ;

    public function __construct()
    {
        $this->username = env('N3TDATA_USER');
        $this->password = env('N3TDATA_PASS');
        $this->baseurl = "https://www.n3tdata.com/api";
    }

    public function generateReference()
    {
        return 'n3t_' . uniqid(time());
    }
    public function getHeader()
    {
        $credentials = base64_encode($this->username.':'.$this->password);

        $response = Http::withHeaders([
            'Authorization' => 'Basic '.$credentials
        ])->post($this->baseurl.'/user' );

        return 'Token ' . $response['AccessToken'];
    }
    public function getUser(){
        $credentials = base64_encode($this->username.':'.$this->password);
        $response = Http::withHeaders([
            'Authorization' => 'Basic '.$credentials
        ])->post($this->baseurl.'/user' )->json();
        return $response;
    }
    public function buyAirtime($data)
    {
        $response = Http::timeout(120)->withHeaders([
            "Content-Type" => "application/json",
            'Authorization' => $this->getHeader()
        ])->post($this->baseurl.'/topup/', $data)->json();

        return $response;

    }
    // buy data
    public function buyData($data)
    {
        $response = Http::timeout(120)->withHeaders([
            "Content-Type" => "application/json",
            'Authorization' => $this->getHeader()
        ])->post($this->baseurl.'/data/', $data)->json();

        return $response;

    }
    // cable sub
    public function buyCablesub($data)
    {
        $response = Http::timeout(120)->withHeaders([
            "Content-Type" => "application/json",
            'Authorization' => $this->getHeader()
        ])->post($this->baseurl.'/cable/', $data)->json();

        return $response;
    }
    // power
    public function buyPower($data)
    {
        $response = Http::timeout(120)->withHeaders([
            "Content-Type" => "application/json",
            'Authorization' => $this->getHeader()
        ])->post($this->baseurl.'/bill/', $data)->json();


        return $response;

    }
    public function sendSMS($data)
    {
        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            'Authorization' => $this->getHeader()
        ])->post($this->baseurl.'/bulksms/', $data)->json();

        return $response;
    }

    //generate Pins
    public function buyExamPins($data)
    {
       $response = Http::timeout(120)->withHeaders([
            "Content-Type" => "application/json",
            'Authorization' => $this->getHeader()
        ])->post($this->baseurl.'/exam/', $data)->json();

        return $response;
    }

    // Buy Datacard
    public function buyDatacard($data)
    {
       $response = Http::timeout(120)->withHeaders([
            "Content-Type" => "application/json",
            'Authorization' => $this->getHeader()
        ])->post($this->baseurl.'/data_card/', $data)->json();

        return $response;
    }

}
