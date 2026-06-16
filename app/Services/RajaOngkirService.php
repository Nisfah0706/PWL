<?php
namespace App\Services;
use Config\Services;

class RajaOngkirService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->client = Services::curlrequest([
            'timeout' => 10,
            'http_errors' => false,
        ]);

        $this->apiKey = env('RAJAONGKIR_API_KEY');
        $this->baseUrl = env('RAJAONGKIR_BASE_URL');
    }
    
    public function getDestination(string $keyword): array
    {
        $response = $this->client->get(
            $this->baseUrl.'destination/domestic-destination',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'key'    => $this->apiKey,
                ],
                'query' => [
                    'search' => $keyword,
                    'limit'  => 50,
                ]
            ]
        );

        return json_decode(
            $response->getBody(),
            true
        );
    }
    
    public function getCost(string $origin, string $destination, int $weight, string $courier): array 
    {
        $response = $this->client->post(
            $this->baseUrl . 'calculate/domestic-cost',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'key'    => $this->apiKey,
                ],
                'form_params' => [
                    'origin'      => $origin,
                    'destination' => $destination,
                    'weight'      => $weight,
                    'courier'     => $courier,
                ]
            ]
        );

        return json_decode(
            $response->getBody(),
            true
        );
    }
}