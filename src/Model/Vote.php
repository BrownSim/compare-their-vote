<?php

namespace App\Model;

class Vote
{
    public \DateTimeInterface $date;

    public string $result;

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
