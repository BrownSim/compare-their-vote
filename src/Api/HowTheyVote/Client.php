<?php

namespace App\Api\HowTheyVote;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    private ?GuzzleClient $client = null;

    private string $apiUrl = 'https://howtheyvote.eu/api/votes';

    public function listVotes(int $page = 1, int $size = 20): array
    {
        return $this->request('GET', '', ['page' => $page, 'page_size' => $size]);
    }

    public function getVote(int $id): array
    {
        return $this->request('GET', '/' . $id);
    }

    private function request(string $method, ?string $uri = '', ?array $params = []): array
    {
        if ('get' === strtolower($method) && !empty($params)) {
            $uri .= '?' . http_build_query($params);
        }
        $request = $this->getClient()->request($method, $this->apiUrl . $uri, $params);

        $response = $request->getBody()->getContents();

        return json_decode($response, true) ?? [];
    }

    private function getClient(): GuzzleClient
    {
        if ($this->client === null) {
            $this->client = new GuzzleClient([
                'base_uri' => $this->apiUrl,
                'timeout'  => 30
            ]);
        }

        return $this->client;
    }
}
