<?php

namespace App\Utility;

use Illuminate\Support\Facades\Http;

class WemaUtility

{
    protected $secretkey;
    protected $subkey;
    protected $baseurl ;

    public function __construct()
    {
        $this->secretkey = env('WEMA_API_KEY');
        $this->subkey = env('WEMA_SUB_KEY');
        $this->baseurl = "https://apiplayground.alat.ng";
    }

    public function generateReference()
    {
        return 'wm_' . uniqid(time());
    }
    public function getHeader()
    {
        $header = array(
            'Content-Type: application/json',
            'Cache-Control' => 'no-cache',
            'api-secret: Bearer '.$this->secretkey,
            'Ocp-Apim-Subscription-Key' => $this->subkey
        );
        return $header;
    }

    // Wallet Creation Request
    public function accountRequest($data)
    {
        $response = Http::withHeaders([
            // 'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
            'x-api-key' => $this->secretkey,
            'Ocp-Apim-Subscription-Key' => $this->subkey
        ])->post($this->baseurl . '/wallet-creation/api/CustomerAccount/GenerateWalletAccountForPartnerships/Request', $data);

        return $response->json();
    }

    // Validate NIN with OTP
    public function validateNinWithOtp($data)
    {
        $response = Http::withHeaders([
            // 'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
            'x-api-key' => $this->secretkey,
            'Ocp-Apim-Subscription-Key' => $this->subkey
        ])->post($this->baseurl . '/wallet-creation/api/CustomerAccount/GenerateWalletAccountForPartnershipsV2/otp', $data);

        return $response->json();
    }

}
