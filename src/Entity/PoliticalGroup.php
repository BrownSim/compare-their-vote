<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class PoliticalGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * @var Collection<int, Member>
     */
    #[ORM\OneToMany(targetEntity: Member::class, mappedBy: 'group')]
    #[ORM\OrderBy(['lastName' => 'ASC', 'firstName' => 'ASC'])]
    private Collection $members;

    /**
     * @var Collection<int, PoliticalGroupVote>
     */
    #[ORM\OneToMany(targetEntity: PoliticalGroupVote::class, mappedBy: 'politicalGroup', orphanRemoval: true)]
    private Collection $politicalGroupVotes;

    #[ORM\Column(type: Types::STRING)]
    private ?string $code = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $label = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $shortLabel = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->politicalGroupVotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): self
    {
        $this->members->add($member);
        $member->setGroup($this);

        return $this;
    }

    private function removeMember(Member $member): self
    {
        $this->members->removeElement($member);
        $member->setGroup(null);

        return $this;
    }

    public function getPoliticalGroupVotes(): Collection
    {
        return $this->politicalGroupVotes;
    }

    public function addPoliticalGroupVote(PoliticalGroupVote $politicalGroupVote): self
    {
        $this->politicalGroupVotes->add($politicalGroupVote);
        $politicalGroupVote->setPoliticalGroup($this);

        return $this;
    }

    private function removePoliticalGroupVote(PoliticalGroupVote $politicalGroupVote): self
    {
        $this->politicalGroupVotes->add($politicalGroupVote);
        $politicalGroupVote->setPoliticalGroup(null);

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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getShortLabel(): ?string
    {
        return $this->shortLabel;
    }

    public function setShortLabel(?string $shortLabel): self
    {
        $this->shortLabel = $shortLabel;

        return $this;
    }
}
