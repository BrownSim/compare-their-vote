<?php

namespace App\Repository;

use App\Entity\Member;
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
            ->addSelect('vote')
            ->addSelect('countries')
            ->leftJoin('m.memberVotes', 'member_votes')
            ->leftJoin('member_votes.vote', 'vote')
            ->leftJoin('vote.countries', 'countries')
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
