<?php

namespace App\Entity;

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

    #[ORM\Column(type: Types::STRING, length: 3)]
    private ?string $code = null;

    #[ORM\Column(type: Types::STRING, length: 2)]
    private ?string $isoAlpha = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $label = null;

    public function getId(): ?int
    {
        return $this->id;
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
