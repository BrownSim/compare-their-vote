<?php

namespace App\Repository;

use App\Entity\Country;
use Doctrine\ORM\EntityRepository;

class MemberVoteStatisticRepository extends EntityRepository
{
    public function findByMemberCountry(?Country $country = null): array
    {
        $query = $this->createQueryBuilder('mvs')
            ->addSelect('m')
            ->addSelect('country')
            ->addSelect('party')
            ->join('mvs.member', 'm')
            ->join('m.group', 'group')
            ->join('m.country', 'country')
            ->join('m.party', 'party')
            ->orderBy('group.position', 'ASC')
        ;

        if (null !== $country) {
            $query
                ->andWhere('country = :c')
                ->setParameter('c', $country)
            ;
        }

        return $query
            ->getQuery()
            ->getResult()
        ;
    }
}
