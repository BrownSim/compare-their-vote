<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Vote;
use Doctrine\ORM\EntityRepository;

class MemberVoteRepository extends EntityRepository
{
    /**
     * @return Vote[]
     */
    public function findFeaturedVotesByMember(Member $member, array $voteValues = [], array $voteIds = []): array
    {
        $query = $this->createQueryBuilder('member_vote')
            ->join('member_vote.vote', 'vote')
            ->where('vote.isFeatured = TRUE')
            ->andWhere('member_vote.member = :member')
            ->setParameter('member', $member)
        ;

        if (!empty($voteValues)) {
            $query
                ->andWhere('member_vote.value IN (:values)')
                ->setParameter('values', $voteValues)
            ;
        }

        if (!empty($voteIds)) {
            $query
                ->andWhere('member_vote.vote IN (:ids)')
                ->setParameter('ids', $voteIds)
            ;
        }

        return $query
            ->getQuery()
            ->getResult()
        ;
    }
}
