<?php

namespace App\Utility;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;

class VtuUtility

{
    protected $password;
    protected $username;
    protected $baseurl ;

    public function __construct()
    {
        $this->username = env('VTUNG_USER');
        $this->password = env('VTUNG_PASS');
        $this->baseurl = "https://vtu.ng/wp-json/api/v1";
    }

    public function generateReference()
    {
        return 'vtg_' . uniqid(time());
    }
    public function getHeader()
    {
        $credentials = base64_encode($this->username.':'.$this->password);

        return 'Token ' . $this->username;
    }
    public function getUser(){

        $data['username'] =  $this->username;
        $data['password'] = $this->password;

        $response = Http::get($this->baseurl.'/balance' , $data)->json();
        return $response;
    }
    public function buyAirtime($data)
    {
        $data['username'] =  $this->username;
        $data['password'] = $this->password;

        $response = Http::timeout(120)->withHeaders([
            "Content-Type" => "application/json",
        ])->get($this->baseurl.'/airtime', $data)->json();

        return $response;

    }
    // buy data
    public function buyData($data)
    {
        $data['username'] =  $this->username;
        $data['password'] = $this->password;
        $response = Http::timeout(120)->withHeaders([
            "Content-Type" => "application/json",
        ])->get($this->baseurl.'/data', $data)->json();

        return $response;

    }
    // cable sub
    public function buyCablesub($data)
    {
        $data['username'] =  $this->username;
        $data['password'] = $this->password;
        $response = Http::timeout(120)->withHeaders([
            "Content-Type" => "application/json",
        ])->get($this->baseurl.'/tv', $data)->json();

        return $response;
    }
    // power
    public function buyPower($data)
    {
        $data['username'] =  $this->username;
        $data['password'] = $this->password;
        $response = Http::timeout(120)->get($this->baseurl.'/electricity', $data)->json();

        return $response;

    }
    //generate Pins
    public function buyExamPins($data)
    {
        $data['username'] =  $this->username;
        $data['password'] = $this->password;

        $response = Http::timeout(120)->withHeaders([
            "Content-Type" => "application/json",
        ])->get($this->baseurl.'/exam/', $data)->json();

        return $response;
    }


}
