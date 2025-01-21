<?php

namespace App\MessageHandler;

use App\Analyser\VoteAnalyser;
use App\Entity\Vote;
use App\Message\VoteToBeAnalysed;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class VoteToBeAnalysedHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VoteAnalyser $analyser
    ) {
    }

    public function __invoke(VoteToBeAnalysed $newVote): void
    {
        $vote = $this->em->getRepository(Vote::class)->find($newVote->getContent());

        if (null === $vote) {
            return;
        }

        $this->analyser->analyse($vote);
    }
}
