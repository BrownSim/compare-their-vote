<?php

namespace App\Model;

class Member
{
    public int $id;

    public string $firstName;

    public string $lastName;

    /**
     * @var Vote[]
     */
    public array $votes = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getVotes(): array
    {
        return $this->votes;
    }
}
