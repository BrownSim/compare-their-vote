<?php

namespace App\Analyser\IA;

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class VoteScanner
{
    public function __construct(
        #[Autowire(env: 'GOOGLE_GEMINI_KEY')]
        private readonly string $apiKey,
    ) {
    }

    public function analyse(string $str)
    {
        $client = new Client($this->apiKey);

        $response = $client->withV1BetaVersion()
            ->generativeModel(ModelName::GEMINI_1_5_FLASH)
            // we need to send instruction to reply only by proposal
            ->withSystemInstruction('Response without explanation, only by given propositions')
            ->generateContent(new TextPart($str))
        ;

        return $this->cleanResponse($response->text());
    }

    private function cleanResponse(?string $str): array
    {
        $responseLines = explode("\n", $str);
        $responseLines = array_filter($responseLines);
        $responseLines = array_map('trim', $responseLines);
        $responseLines = array_map('strtolower', $responseLines);

        return $responseLines;
    }
}
