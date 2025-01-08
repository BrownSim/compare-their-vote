<?php

namespace App\Model;

class Vote
{
    public ?int $id;

    public string $result;

    public \DateTimeInterface $date;

    /**
     * @var Country[]
     */
    public array $countries = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getCountries(): array
    {
        return $this->countries;
    }
}
