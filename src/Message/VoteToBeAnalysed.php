<?php

namespace App\Message;

use App\Entity\Vote;

class VoteToBeAnalysed
{
    public function __construct(
        private readonly int $voteId,
    ) {
    }

    public function getContent(): int
    {
        return $this->voteId;
    }
}
