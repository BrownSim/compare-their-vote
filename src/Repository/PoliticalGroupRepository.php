<?php

namespace App\Repository;

use App\Entity\Country;
use App\Entity\PoliticalGroup;
use Doctrine\ORM\EntityRepository;

class PoliticalGroupRepository extends EntityRepository
{
    public function findByMemberCountry(?Country $country = null): array
    {
        $query = $this->createQueryBuilder('pg')
            ->join('pg.members', 'members')
            ->join('members.country', 'country')
        ;

        if (null !== $country) {
            $query
                ->where('country = :c')
                ->setParameter('c', $country)
            ;
        }

        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array|PoliticalGroup[]
     */
    public function findPoliticalGroupWithMpWithVotes(): array
    {
        return $this->createQueryBuilder('pg')
            ->addSelect('members')
            ->addSelect('member_votes')
            ->addSelect('vote')
            ->innerJoin('pg.members', 'members')
            ->join('members.memberVotes', 'member_votes')
            ->join('member_votes.vote', 'vote')
            ->getQuery()
            ->getResult()
            ;
    }
}
