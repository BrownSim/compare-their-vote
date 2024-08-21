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
        $orderedIds = [];

        foreach ($data as $datum) {
            // keep the mysql order for reorder the matrix
            $orderedIds[$datum['member_1_id']] = true;

            $matrix[$datum['member_1_id']][$datum['member_1_id']] = [];
            $matrix[$datum['member_1_id']][$datum['member_2_id']] = [
                'member_1' => $datum['member_1_id'],
                'member_2' => $datum['member_2_id'],
                'rate' => $datum['agreement_rate'],
                'nb_vote' => $datum['nb_vote'],
            ];
        }

        // reorder the matrix
        foreach ($matrix as $key => $line) {
            $matrix[$key] = array_replace($orderedIds, $line);
        }

        return $matrix;
    }

    public function findMatrixMember(array $matrix): array
    {
        $members = [];
        $listMembers = $this->em->getRepository(Member::class)->findMembersByIds(array_keys($matrix));

        foreach ($listMembers as $member) {
            $members[$member->getId()] = $member;
        }

        return $members;
    }
}
