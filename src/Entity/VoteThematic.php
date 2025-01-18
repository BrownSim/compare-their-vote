<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class VoteThematic
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\ManyToMany(targetEntity: Vote::class, mappedBy: 'thematics')]
    private Collection $votes;

    /**
     * @var Collection<int, VoteThematicCategory>
     */
    #[ORM\ManyToMany(targetEntity: VoteThematicCategory::class, inversedBy: 'voteThematics')]
    #[ORM\JoinTable(name: 'vote_thematic_has_category')]
    private ?Collection $categories;

    #[ORM\OneToOne(targetEntity: VoteThematicPrompt::class, inversedBy: 'voteThematic')]
    #[ORM\JoinColumn(name: 'prompt_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private VoteThematicPrompt $prompt;

    #[ORM\Column(type: Types::STRING)]
    private ?string $label = null;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $votes): self
    {
        $this->votes->add($votes);

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        $this->votes->removeElement($vote);

        return $this;
    }

    public function getCategories(): ?Collection
    {
        return $this->categories;
    }

    public function addCategory(VoteThematicCategory $category): self
    {
        $this->categories->add($category);

        return $this;
    }

    public function removeCategory(VoteThematicCategory $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getPrompt(): VoteThematicPrompt
    {
        return $this->prompt;
    }

    public function setPrompt(VoteThematicPrompt $prompt): self
    {
        $this->prompt = $prompt;

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
