<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * @var Collection<int, MemberVote>
     */
    #[ORM\OneToMany(targetEntity: MemberVote::class, mappedBy: 'vote', orphanRemoval: true)]
    private Collection $membersVote;

    /**
     * @var Collection<int, PoliticalGroup>
     */
    #[ORM\OneToMany(targetEntity: PoliticalGroupVote::class, mappedBy: 'vote', orphanRemoval: true)]
    private Collection $politicalGroupVote;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $officialId = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isFeatured = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $voteDate = null;

    public function __construct()
    {
        $this->membersVote = new ArrayCollection();
        $this->politicalGroupVote = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMembersVote(): Collection
    {
        return $this->membersVote;
    }

    public function addMemberVote(MemberVote $memberVote): self
    {
        $this->membersVote->add($memberVote);
        $memberVote->setVote($this);

        return $this;
    }

    public function removeMemberVote(MemberVote $memberVote): self
    {
        $this->membersVote->removeElement($memberVote);
        $memberVote->setVote(null);

        return $this;
    }

    public function getPoliticalGroupVotes(): Collection
    {
        return $this->politicalGroupVote;
    }

    public function addPoliticalGroupVote(PoliticalGroupVote $politicalGroupVote): self
    {
        $this->politicalGroupVote->add($politicalGroupVote);
        $politicalGroupVote->setVote($this);

        return $this;
    }

    public function removePoliticalGroupVote(PoliticalGroupVote $politicalGroupVote): self
    {
        $this->politicalGroupVote->removeElement($politicalGroupVote);
        $politicalGroupVote->setVote(null);

        return $this;
    }

    public function getOfficialId(): ?int
    {
        return $this->officialId;
    }

    public function setOfficialId(?int $officialId): self
    {
        $this->officialId = $officialId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIsFeatured(): ?bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(?bool $isFeatured): self
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

    public function getVoteDate(): ?\DateTimeImmutable
    {
        return $this->voteDate;
    }

    public function setVoteDate(?\DateTimeImmutable $voteDate): self
    {
        $this->voteDate = $voteDate;

        return $this;
    }
}
