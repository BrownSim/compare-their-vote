<?php

namespace App\Normaliser\TomSelect;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;

class MemberNormalizer
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function membersToTomSelect(): string
    {
        $members = [];
        foreach ($this->em->getRepository(Member::class)->findAll() as $member) {
            $members[] = [
                'value' => $member->getId(),
                'name' => $member->getFirstName() . ' ' . $member->getLastName(),
                'group' => $member->getGroup()->getShortLabel()
            ];
        }

        return json_encode($members);
    }
}
