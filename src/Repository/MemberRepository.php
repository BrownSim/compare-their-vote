<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\MemberVote;
use Doctrine\ORM\EntityRepository;

class MemberRepository extends EntityRepository
{
    /**
     * For import, load all members votes is faster than perform one by one query
     *
     * @return Member[]
     */
    public function findMembersWithVotes(): array
    {
        return $this->createQueryBuilder('m')
            ->addSelect('member_votes')
            ->join('m.memberVotes', 'member_votes')
            ->join('member_votes.vote', 'vote')
            ->where('vote.isFeatured = TRUE')
            ->andWhere('member_votes.value in (:values)')
            ->setParameter('values', [MemberVote::VOTE_FOR, MemberVote::VOTE_AGAINST, MemberVote::VOTE_ABSTENTION])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMembersByIds(array $ids): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }
}
