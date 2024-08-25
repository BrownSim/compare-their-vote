<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\ManyToMany(targetEntity: Vote::class, mappedBy: 'countries')]
    private Collection $relatedVotes;

    /**
     * @var Collection<int, Member>
     */
    #[ORM\OneToMany(targetEntity: Member::class, mappedBy: 'country')]
    private Collection $members;

    #[ORM\Column(type: Types::STRING, length: 3)]
    private ?string $code = null;

    #[ORM\Column(type: Types::STRING, length: 2)]
    private ?string $isoAlpha = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $label = null;

    public function __construct()
    {
        $this->relatedVotes = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRelatedVotes(): Collection
    {
        return $this->relatedVotes;
    }

    public function addVote(Vote $vote): self
    {
        $this->relatedVotes->add($vote);

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        $this->relatedVotes->removeElement($vote);

        return $this;
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): self
    {
        $this->members->add($member);
        $member->setCountry($this);

        return $this;
    }

    public function removeMember(Member $member): self
    {
        $this->members->removeElement($member);
        $member->setCountry(null);

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getIsoAlpha(): ?string
    {
        return $this->isoAlpha;
    }

    public function setIsoAlpha(?string $isoAlpha): self
    {
        $this->isoAlpha = $isoAlpha;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
