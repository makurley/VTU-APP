<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class SudoService
{
    protected $baseUrl;
    protected $publicKey;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = env('SUDO_BASE_URL');
        $this->publicKey = env('SUDO_PUBLIC_KEY');
        $this->secretKey = env('SUDO_SECRET_KEY');
    }

    protected function getAuthHeader()
    {
        return base64_encode("{$this->publicKey}:{$this->secretKey}");
    }

    public function createCustomer($data)
    {
        return Http::withHeaders([
            'Authorization' => 'Basic ' . $this->getAuthHeader(),
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/customers", $data)->json();
    }

    public function issueCard($data)
    {
        return Http::withHeaders([
            'Authorization' => 'Basic ' . $this->getAuthHeader(),
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/cards", $data)->json();
    }

    // Add more API methods here as needed (fetch cards, block cards, etc.)
    
        protected $baseUrl = 'https://api.sudo.africa';

    protected function headers()
    {
        return [
            'Content-Type' => 'application/json',
            'api-key' => env('SUDO_API_KEY'),
        ];
    }

    public function createCustomer($data)
    {
        return Http::withHeaders($this->headers())
            ->post("$this->baseUrl/customers", $data)
            ->json();
    }

    public function issueCard($customerRef)
    {
        return Http::withHeaders($this->headers())
            ->post("$this->baseUrl/cards", [
                'customer_ref' => $customerRef,
            ])
            ->json();
    }

    public function fundCard($cardRef, $amount)
    {
        return Http::withHeaders($this->headers())
            ->post("$this->baseUrl/cards/fund", [
                'card_ref' => $cardRef,
                'amount' => $amount,
            ])
            ->json();
    }

    // Add more methods like get card details, freeze, terminate, etc.


}


