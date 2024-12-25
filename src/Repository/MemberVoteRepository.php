<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Vote;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class MemberVoteRepository extends EntityRepository
{
    /**
     * @return Vote[]
     */
    public function findFeaturedVotesByMember(Member $member, array $voteValues = [], array $voteIds = []): array
    {
        $query = $this->createQueryBuilder('member_vote')
            ->addSelect('vote')
            ->join('member_vote.vote', 'vote')
            ->where('member_vote.member = :member')
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

    public function getVoteResultByMemberQuery(Member $member): Query
    {
        return $this->createQueryBuilder('member_vote')
            ->join('member_vote.vote', 'vote')
            ->where('member_vote.member = :member')
            ->orderBy('vote.voteDate', 'DESC')
            ->setParameter('member', $member)
            ->getQuery()
        ;
    }

    public function countMemberVoteByValue(Member $member, ...$voteValues): int
    {
        return $this->createQueryBuilder('member_vote')
            ->select('COUNT(member_vote)')
            ->where('member_vote.member = :member')
            ->andWhere('member_vote.value IN (:values)')
            ->setParameter('member', $member)
            ->setParameter('values', $voteValues)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findMemberVoteWithVote(Member $member): array
    {
        return $this->createQueryBuilder('member_vote')
            ->select('member_vote.value as vote_value')
            ->addSelect('vote.voteDate as vote_date')
            ->join('member_vote.vote', 'vote')
            ->join('member_vote.member', 'member')
            ->where('member_vote.member = :member')
            ->orderBy('vote_date')
            ->orderBy('member.lastName')
            ->setParameter('member', $member)
            ->getQuery()
            ->getResult()
        ;
    }
}
