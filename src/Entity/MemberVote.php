<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class MemberVote
{
    public const VOTE_FOR = 1;
    public const VOTE_AGAINST = 2;
    public const VOTE_ABSTENTION = 3;
    public const VOTE_DID_NOT_VOTE = 4;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'memberVotes')]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id')]
    private ?Member $member = null;

    #[ORM\ManyToOne(targetEntity: Vote::class, inversedBy: 'membersVotes')]
    #[ORM\JoinColumn(name: 'vote_id', referencedColumnName: 'id')]
    private ?Vote $vote = null;

    #[ORM\Column(type: Types::STRING, length: 12)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
