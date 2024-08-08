<?php

namespace App\Repository;

use App\Entity\PoliticalGroup;
use App\Entity\PoliticalGroupVote;
use App\Entity\Vote;
use Doctrine\ORM\EntityRepository;

class PoliticalGroupVoteRepository extends EntityRepository
{
    public function findByGroupAndVote(PoliticalGroup $politicalGroup, Vote $vote): PoliticalGroupVote
    {
        return $this->createQueryBuilder('pgv')
            ->join('pgv.politicalGroup', 'political_group')
            ->join('pgv.vote', 'vote')
            ->where('political_group = :political_group')
            ->andWhere('vote = :vote')
            ->setParameter('political_group', $politicalGroup)
            ->setParameter('vote', $vote)
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
