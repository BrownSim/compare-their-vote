<?php

namespace App\Repository;

use App\Entity\Country;
use App\Entity\Member;
use App\Entity\PoliticalGroup;
use Doctrine\ORM\EntityRepository;

class MemberToMemberVoteComparisonRepository extends EntityRepository
{
    /**
     * classic sql query is used to improve speed
     */
    public function compareByCountry(Country $country): array
    {
        $sql = 'SELECT * 
                FROM member_to_member_vote_comparison m0_ 
                JOIN member as member_1 on m0_.member_1_id = member_1.id
                WHERE m0_.country_member_1_id = :country
                AND m0_.country_member_2_id = :country
                ORDER BY m0_.group_member_1_id, member_1.last_name
            ';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $results = $stmt->executeQuery(['country' => $country->getId()]);

        return $results->fetchAllAssociative();
    }

    /**
     * classic sql query is used to improve speed
     */
    public function compareByGroup(PoliticalGroup $group): array
    {
        $sql = 'SELECT * 
                FROM member_to_member_vote_comparison m0_ 
                JOIN member as member_1 on m0_.member_1_id = member_1.id
                WHERE m0_.group_member_1_id = :group
                AND m0_.group_member_2_id = :group
                ORDER BY m0_.country_member_1_id, member_1.last_name
            ';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $results = $stmt->executeQuery(['group' => $group->getId()]);

        return $results->fetchAllAssociative();
    }
}
