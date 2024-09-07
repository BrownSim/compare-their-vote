<?php

namespace App\Repository;

use App\Entity\Country;
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
}
