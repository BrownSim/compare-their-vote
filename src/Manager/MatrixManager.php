<?php

namespace App\Manager;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;

class MatrixManager
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function generateMatrix(array $data): array
    {
        $matrix = [];
        $ids = [];

        $members = [];
        /** @var Member $member */
        foreach ($this->em->getRepository(Member::class)->findAll() as $member) {
            $members[$member->getId()] = $member;
        }

        foreach ($data as $datum) {
            $member1 = $members[$datum['member_1_id']];
            $member2 = $members[$datum['member_2_id']];

            $ids[$this->generateMemberKey($member1)] = $member1;
            $ids[$this->generateMemberKey($member2)] = $member2;

            $matrix[$this->generateMemberKey($member1)][$this->generateMemberKey($member1)] = [
                'memberY' => $member1,
                'memberX' => $member1,
            ];

            $matrix[$this->generateMemberKey($member2)][$this->generateMemberKey($member2)] = [
                'memberY' => $member2,
                'memberX' => $member2,
            ];

            $matrix[$this->generateMemberKey($member1)][$this->generateMemberKey($member2)] = [
                'memberY' => $member2,
                'memberX' => $member1,
                'rate' => $datum['agreement_rate'],
                'nb_vote' => $datum['nb_vote'],
            ];

            $matrix[$this->generateMemberKey($member2)][$this->generateMemberKey($member1)] = [
                'memberY' => $member1,
                'memberX' => $member2,
                'rate' => $datum['agreement_rate'],
                'nb_vote' => $datum['nb_vote'],
            ];
        }

        foreach ($matrix as $memberXId => &$line) {
            foreach ($ids as $memberYId => $member) {
                if (!isset($line[$memberYId])) {
                    $line[$memberYId] = [
                        'memberX' => $ids[$memberXId],
                        'memberY' => $member,
                        'rate' => 0,
                        'nb_vote' => 0
                    ];
                }
            }
        }

        foreach ($matrix as &$line) {
            ksort($line);
        }

        ksort($matrix);

        return $matrix;
    }

    private function generateMemberKey(Member $member)
    {
        return $member->getGroup()->getPosition() . $member->getLastName() . $member->getLastName() . $member->getId();
    }
}
