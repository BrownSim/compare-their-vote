<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class VoteThematicAnalysed
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vote::class)]
    #[ORM\JoinColumn(name: 'vote_id', referencedColumnName: 'id')]
    private ?Vote $vote = null;

    #[ORM\ManyToOne(targetEntity: VoteThematicPrompt::class)]
    #[ORM\JoinColumn(name: 'prompt_id', referencedColumnName: 'id')]
    private ?VoteThematicPrompt $prompt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $analysedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVote(): ?Vote
    {
        return $this->vote;
    }

    public function setVote(?Vote $vote): self
    {
        $this->vote = $vote;

        return $this;
    }

    public function getPrompt(): ?VoteThematicPrompt
    {
        return $this->prompt;
    }

    public function setPrompt(?VoteThematicPrompt $prompt): self
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function getAnalysedAt(): \DateTimeImmutable
    {
        return $this->analysedAt;
    }

    public function setAnalysedAt(\DateTimeImmutable $analysedAt): self
    {
        $this->analysedAt = $analysedAt;

        return $this;
    }
}
