<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class VoteThematicCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VoteThematicCategory::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    private ?VoteThematicCategory $parent;

    /**
     * @var Collection<int, VoteThematicCategory>
     */
    #[ORM\OneToMany(targetEntity: VoteThematicCategory::class, mappedBy: 'parent')]
    private Collection $children;

    /**
     * @var Collection<int, VoteThematic>
     */
    #[ORM\ManyToMany(targetEntity: VoteThematic::class, mappedBy: 'voteThemeCategory')]
    private Collection $voteThematics;

    #[ORM\Column(type: Types::STRING)]
    private ?string $label = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->voteThematics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?VoteThematicCategory
    {
        return $this->parent;
    }

    public function setParent(?VoteThematicCategory $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChildren(VoteThematicCategory $category): self
    {
        $this->children->add($category);

        return $this;
    }

    public function removeChild(VoteThematicCategory $category): self
    {
        $this->children->removeElement($category);

        return $this;
    }

    public function getVoteThematics(): Collection
    {
        return $this->voteThematics;
    }

    public function addVoteTheme(VoteThematic $voteTheme): self
    {
        $this->voteThematics->add($voteTheme);

        return $this;
    }

    public function removeVoteTheme(VoteThematic $voteTheme): self
    {
        $this->voteThematics->removeElement($voteTheme);

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
