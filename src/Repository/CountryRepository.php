<?php

namespace App\Repository;

use App\Entity\Country;
use Doctrine\ORM\EntityRepository;

class CountryRepository extends EntityRepository
{
    /**
     * @return array|Country[]
     */
    public function findCountriesWithMpWithVotes(): array
    {
        return $this->createQueryBuilder('country')
            ->addSelect('members')
            ->addSelect('member_votes')
            ->addSelect('vote')
            ->innerJoin('country.members', 'members')
            ->join('members.memberVotes', 'member_votes')
            ->join('member_votes.vote', 'vote')
            ->getQuery()
            ->getResult()
        ;
    }
}
