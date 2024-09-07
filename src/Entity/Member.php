<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
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

    #[ORM\ManyToOne(targetEntity: PoliticalGroup::class, inversedBy: 'members')]
    private ?PoliticalGroup $group = null;

    #[ORM\ManyToOne(targetEntity: Country::class, inversedBy: 'members')]
    private ?Country $country = null;

    #[ORM\ManyToOne(targetEntity: Party::class, inversedBy: 'members')]
    #[ORM\JoinColumn(name: 'party_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?Party $party = null;

    #[ORM\OneToOne(targetEntity: MemberVoteStatistic::class, mappedBy: 'member')]
    private ?MemberVoteStatistic $voteStatistics = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isActive = false;

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

    /**
     * @return Collection<int, MemberVote>
     */
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

    public function getGroup(): ?PoliticalGroup
    {
        return $this->group;
    }

    public function setGroup(?PoliticalGroup $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function setParty(?Party $party): self
    {
        $this->party = $party;

        return $this;
    }

    public function getVoteStatistics(): ?MemberVoteStatistic
    {
        return $this->voteStatistics;
    }

    public function setVoteStatistics(?MemberVoteStatistic $voteStatistics): self
    {
        $this->voteStatistics = $voteStatistics;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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
