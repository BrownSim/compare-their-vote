<?php

namespace App\Analyser;

use App\Analyser\IA\VoteScanner;
use App\Entity\Vote;
use App\Entity\VoteThematicAnalysed;
use App\Entity\VoteThematicPrompt;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class VoteAnalyser
{
    private array $listPrompts = [];

    public function __construct(
        private readonly VoteScanner $voteScanner,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function analyse(Vote $vote): void
    {
        $client = new Client();
        $prompts = $this->em->getRepository(VoteThematicPrompt::class)->findAll();

        $crawler = new Crawler($client->get($vote->getSummaryLink())->getBody()->getContents());
        $voteStr = $crawler->filter('#website-body')->text();

        $voteStr .= '\n';
        $voteStr .= 'Response without explanation';
        $voteStr .= '\n';

        foreach ($prompts as $prompt) {
            $this->listPrompts[] = $prompt;

            $questionStr = $prompt->getQuestion() . ' ' . implode(' or ', $prompt->getResponses());
            $voteStr .= $questionStr;
            $voteStr .= '\n';

            $voteAnalysed = (new VoteThematicAnalysed())
                ->setVote($vote)
                ->setPrompt($prompt)
                ->setAnalysedAt(new \DateTimeImmutable())
            ;

            $this->em->persist($voteAnalysed);
        }

        $responses = $this->voteScanner->analyse($voteStr);

        $i = 0;
        foreach ($responses as $response) {
            $prompt = $this->listPrompts[$i] ?? null;
            $i++;

            if (null === $prompt || $response !== 'true') {
                continue;
            }

            $vote->addThematic($prompt->getThematic());
        }

        $this->em->flush();
    }
}
