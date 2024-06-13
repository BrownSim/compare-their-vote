<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * @var Collection<int, MemberVote>
     */
    #[ORM\OneToMany(targetEntity: MemberVote::class, mappedBy: 'member', orphanRemoval: true)]
    private Collection $memberVotes;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $mepId = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $thumb = null;

    public function __construct()
    {
        $this->memberVotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMemberVotes(): Collection
    {
        return $this->memberVotes;
    }

    public function addMemberVote(MemberVote $memberVote): self
    {
        $this->memberVotes->add($memberVote);
        $memberVote->setMember($this);

        return $this;
    }

    public function removeMemberVote(MemberVote $memberVote): self
    {
        $this->memberVotes->removeElement($memberVote);
        $memberVote->setMember(null);

        return $this;
    }

    public function getMepId(): ?int
    {
        return $this->mepId;
    }

    public function setMepId(?int $mepId): self
    {
        $this->mepId = $mepId;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getThumb(): ?string
    {
        return $this->thumb;
    }

    public function setThumb(?string $thumb): self
    {
        $this->thumb = $thumb;

        return $this;
    }
}
