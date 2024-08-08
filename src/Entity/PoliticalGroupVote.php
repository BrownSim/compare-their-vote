<?php

namespace App\Entity;

use App\Repository\PoliticalGroupVoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PoliticalGroupVoteRepository::class)]
#[ORM\Table]
class PoliticalGroupVote
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PoliticalGroup::class, inversedBy: 'politicalGroupVotes', fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id')]
    private ?PoliticalGroup $politicalGroup = null;

    #[ORM\ManyToOne(targetEntity: Vote::class, inversedBy: 'politicalGroupVote', fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'vote_id', referencedColumnName: 'id')]
    private ?Vote $vote = null;

    #[ORM\Column(type: Types::JSON)]
    private array $stats = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoliticalGroup(): ?PoliticalGroup
    {
        return $this->politicalGroup;
    }

    public function setPoliticalGroup(?PoliticalGroup $politicalGroup): self
    {
        $this->politicalGroup = $politicalGroup;

        return $this;
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

    public function getStats(): array
    {
        return $this->stats;
    }

    public function setStats(array $stats): self
    {
        $this->stats = $stats;

        return $this;
    }
}
