<?php

namespace App\Entity;

use App\Repository\MemberVoteStatisticRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberVoteStatisticRepository::class)]
#[ORM\Table]
class MemberVoteStatistic
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Member $member = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $miss = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $attendance = null;

    private ?int $attendancePrediction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getMiss(): ?int
    {
        return $this->miss;
    }

    public function setMiss(?int $miss): self
    {
        $this->miss = $miss;

        return $this;
    }

    public function getAttendance(): ?int
    {
        return $this->attendance;
    }

    public function setAttendance(?int $attendance): self
    {
        $this->attendance = $attendance;

        return $this;
    }

    public function getNbVote(): int
    {
        return $this->getMiss() + $this->getAttendance();
    }

    public function getMissRatio(): ?float
    {
        return ($this->getMiss() * 100) / $this->getNbVote();
    }

    public function getAttendancePrediction(?int $round = null): float|int
    {
        if ($round) {
            return round($this->attendancePrediction, $round);
        }

        return $this->attendancePrediction;
    }

    public function setAttendancePrediction(?int $attendancePrediction): self
    {
        $this->attendancePrediction = $attendancePrediction;

        return $this;
    }

    public function getAttendanceGapWithPrediction(?int $round = null): float|int
    {
        if ($round) {
            return round($this->getAttendance() - $this->attendancePrediction, $round);
        }

        return $this->getAttendance() - $this->attendancePrediction;
    }
}
