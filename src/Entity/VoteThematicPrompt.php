<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class VoteThematicPrompt
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: VoteThematic::class, mappedBy: 'prompt')]
    private ?VoteThematic $thematic = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $question = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $responses = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getThematic(): ?VoteThematic
    {
        return $this->thematic;
    }

    public function setThematic(?VoteThematic $thematic): self
    {
        $this->thematic = $thematic;
        $thematic->setPrompt($this);

        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(?string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function setResponses(array $responses): self
    {
        $this->responses = $responses;

        return $this;
    }
}
