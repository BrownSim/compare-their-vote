<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\Table]
#[ORM\Index(columns: ['is_featured'])]
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

    /**
     * @var Collection<int, Country>
     */
    #[ORM\ManyToMany(targetEntity: Country::class, inversedBy: 'relatedVotes')]
    #[ORM\JoinTable(name: 'votes_countries')]
    private Collection $countries;

    #[ORM\ManyToMany(targetEntity: GeoArea::class, inversedBy: 'votes')]
    #[ORM\JoinTable(name: 'votes_geoareas')]
    private Collection $geoAreas;

    /**
     * @var Collection<int, VoteThematic>
     */
    #[ORM\ManyToMany(targetEntity: VoteThematic::class, inversedBy: 'votes')]
    #[ORM\JoinTable(name: 'vote_has_thematic')]
    private Collection $thematics;

    #[ORM\OneToMany(targetEntity: VoteThematicAnalysed::class, mappedBy: 'vote')]
    private Collection $thematicsAnalysed;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $officialId = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isFeatured = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $procedureReference = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $summaryLink = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $voteDate = null;

    public function __construct()
    {
        $this->geoAreas = new ArrayCollection();
        $this->countries = new ArrayCollection();
        $this->membersVote = new ArrayCollection();
        $this->politicalGroupVote = new ArrayCollection();
        $this->thematics = new ArrayCollection();
        $this->thematicsAnalysed = new ArrayCollection();
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

    /**
     * @return Collection<int, Country>
     */
    public function getCountries(): Collection
    {
        return $this->countries;
    }

    public function addCountry(Country $country): self
    {
        $this->countries->add($country);

        return $this;
    }

    public function removeCountry(Country $country): self
    {
        $this->countries->removeElement($country);

        return $this;
    }

    public function getGeoAreas(): Collection
    {
        return $this->geoAreas;
    }

    public function addGeoArea(GeoArea $geoArea): self
    {
        $this->geoAreas->add($geoArea);

        return $this;
    }

    public function removeGeoArea(GeoArea $geoArea): self
    {
        $this->geoAreas->removeElement($geoArea);

        return $this;
    }

    public function getThematics(): Collection
    {
        return $this->thematics;
    }

    public function addThematic(VoteThematic $thematic): self
    {
        $this->thematics->add($thematic);

        return $this;
    }

    public function removeThematic(VoteThematic $thematic): self
    {
        $this->thematics->removeElement($thematic);

        return $this;
    }

    public function getThematicsAnalysed(): Collection
    {
        return $this->thematicsAnalysed;
    }

    public function getThematicAnalysed(VoteThematicAnalysed $thematic): self
    {
        $this->thematicsAnalysed->add($thematic);
        $thematic->setVote($this);

        return $this;
    }

    public function removeThematicAnalysed(VoteThematicAnalysed $thematic): self
    {
        $this->thematicsAnalysed->removeElement($thematic);
        $thematic->setVote(null);

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

    public function getProcedureReference(): ?string
    {
        return $this->procedureReference;
    }

    public function setProcedureReference(?string $procedureReference): self
    {
        $this->procedureReference = $procedureReference;

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

    public function getSummaryLink(): ?string
    {
        return $this->summaryLink;
    }

    public function setSummaryLink(?string $summaryLink): self
    {
        $this->summaryLink = $summaryLink;

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
