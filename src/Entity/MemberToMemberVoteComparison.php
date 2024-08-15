<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class MemberToMemberVoteComparison
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_1_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Member $member1 = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_2_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Member $member2 = null;

    #[ORM\ManyToOne(targetEntity: PoliticalGroup::class)]
    #[ORM\JoinColumn(name: 'group_member_1_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?PoliticalGroup $groupMember1 = null;

    #[ORM\ManyToOne(targetEntity: PoliticalGroup::class)]
    #[ORM\JoinColumn(name: 'group_member_2_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?PoliticalGroup $groupMember2 = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name: 'country_member_1_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?Country $countryMember1 = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name: 'country_member_2_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?Country $countryMember2 = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $nbVote = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $agreementRate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember1(): ?Member
    {
        return $this->member1;
    }

    public function setMember1(?Member $member1): self
    {
        $this->member1 = $member1;

        return $this;
    }

    public function getMember2(): ?Member
    {
        return $this->member2;
    }

    public function setMember2(?Member $member2): self
    {
        $this->member2 = $member2;

        return $this;
    }

    public function getGroupMember1(): ?PoliticalGroup
    {
        return $this->groupMember1;
    }

    public function setGroupMember1(?PoliticalGroup $groupMember1): self
    {
        $this->groupMember1 = $groupMember1;

        return $this;
    }

    public function getGroupMember2(): ?PoliticalGroup
    {
        return $this->groupMember2;
    }

    public function setGroupMember2(?PoliticalGroup $groupMember2): self
    {
        $this->groupMember2 = $groupMember2;

        return $this;
    }

    public function getCountryMember1(): ?Country
    {
        return $this->countryMember1;
    }

    public function setCountryMember1(?Country $countryMember1): self
    {
        $this->countryMember1 = $countryMember1;

        return $this;
    }

    public function getCountryMember2(): ?Country
    {
        return $this->countryMember2;
    }

    public function setCountryMember2(?Country $countryMember2): self
    {
        $this->countryMember2 = $countryMember2;

        return $this;
    }

    public function getNbVote(): ?int
    {
        return $this->nbVote;
    }

    public function setNbVote(?int $nbVote): self
    {
        $this->nbVote = $nbVote;

        return $this;
    }

    public function getAgreementRate(): ?float
    {
        return $this->agreementRate;
    }

    public function setAgreementRate(?float $agreementRate): self
    {
        $this->agreementRate = $agreementRate;

        return $this;
    }
}
