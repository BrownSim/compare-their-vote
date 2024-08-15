<?php

namespace App\Controller;

use App\Entity\Member;
use App\Manager\MemberManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class MemberAndGroupStat extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    #[Route('/member-group', name: 'member-group')]
    public function show(Request $request, MemberManager $memberManager)
    {
        $sql = 'SELECT * 
                FROM member_to_member_vote_comparison m0_ 
                WHERE m0_.group_member_1_id = 1
                AND m0_.group_member_2_id = 1
            ';

        $stmt = $this->em->getConnection()->prepare($sql);
        $data = $stmt->executeQuery()->fetchAllAssociative();
        $matrix = $this->generateMatrix($data);

        return $this->render('matrix/index.html.twig', [
            'matrix' => $matrix,
            'members' => $this->findMatrixMember($matrix),
        ]);
    }

    private function generateMatrix(array $data): array
    {
        $matrix = [];


        foreach ($data as $datum) {
            $matrix[$datum['member_1_id']][$datum['member_1_id']] = [];
            $matrix[$datum['member_1_id']][$datum['member_2_id']] = [
                'member_1' => $datum['member_1_id'],
                'member_2' => $datum['member_2_id'],
                'rate' => $datum['agreement_rate'],
                'nb_vote' => $datum['nb_vote'],
            ];

            ksort($matrix[$datum['member_1_id']]);
        }

        return $matrix;
    }

    private function findMatrixMember(array $matrix): array
    {
        $members = [];
        $listMembers = $this->em->getRepository(Member::class)->findMembersByIds(array_keys($matrix));

        foreach ($listMembers as $member) {
            $members[$member->getId()] = $member;
        }

        return $members;
    }
}
