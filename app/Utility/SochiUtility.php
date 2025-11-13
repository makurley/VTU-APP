<?php

namespace App\Utility;

use Illuminate\Support\Facades\Http;

class SochiUtility

{
    protected $password ;
    protected $username;
    protected $baseurl ;

    public function __construct()
    {
        $this->username = env('SOCHI_USERNAME');
        $this->password = env('SOCHI_PASSWORD');
        $this->baseurl = "https://artx.sochitel.com/api.php";
        // $this->baseurl = "https://artx.sochitel.com/staging.php";

    }

    public function getRequestAuth()
    {
        $salt = bin2hex(random_bytes(40 / 2));
        $credentials = [
            "username" => $this->username,
            "salt" => $salt,
            "password" => $this->hashPassword($this->password, $salt)
        ];

        return $credentials;
    }

    private function hashPassword($password, $salt) {
        $sha1PasswordHash = sha1($password);

        $saltedPassword = $salt . $sha1PasswordHash;

        return sha1($saltedPassword);
    }

    function getOperators(){
        $data = null;
        $data['auth'] = $this->getRequestAuth();
        $data["version"] = "5";
	    $data["command"] = "getOperators";
        // return $data;
        $response = Http::timeout(120)->post($this->baseurl, $data)->json();

        return $response;
    }

    function getBalance(){
        $data = null;
        $data['auth'] = $this->getRequestAuth();
        $data["version"] = "5";
	    $data["command"] = "getBalance";
        // return $data;
        $response = Http::timeout(120)->post($this->baseurl, $data)->json();

        return $response;
    }

    function makeRequest($command, $data = null){
        $data['auth'] = $this->getRequestAuth();
	    $data["command"] = $command;
        $data["version"] = "5";
        $response = Http::timeout(120)->post($this->baseurl, $data)->json();

        return $response;
    }
}
