<?php

namespace App\Api\HowTheyVote;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    public const SESSION_STATUS_PAST = 'past';
    public const SESSION_STATUS_UPCOMING = 'upcoming';

    private ?GuzzleClient $client = null;

    private string $apiUrl = 'https://howtheyvote.eu/api/';

    public function listVotes(int $page = 1, int $size = 20): array
    {
        return $this->request('GET', 'votes', ['page' => $page, 'page_size' => $size]);
    }

    public function getVote(int $id): array
    {
        return $this->request('GET', 'votes/' . $id);
    }

    public function getListSessions(int $page, int $pageSize, string $order, string $status): array
    {
        return $this->request('GET', 'sessions', [
            'page' => $page,
            'page_size' => $pageSize,
            'sort_order' => $order,
            'status' => $status
        ]);
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
