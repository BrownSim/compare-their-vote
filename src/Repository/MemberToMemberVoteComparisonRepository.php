<?php

namespace App\Repository;

use App\Entity\Country;
use App\Entity\PoliticalGroup;
use Doctrine\ORM\EntityRepository;

class MemberToMemberVoteComparisonRepository extends EntityRepository
{
    /**
     * classic sql query is used to improve speed
     */
    public function compareByCountry(Country $mpCountry, ?Country $country, ?int $minNbVote = 5): array
    {
        $sql = 'SELECT * 
                FROM member_to_member_vote_comparison m0_ 
                JOIN member as member_1 on m0_.member_1_id = member_1.id
                JOIN political_group ON member_1.group_id = political_group.id
                WHERE m0_.country_member_1_id = :mpCountry
                AND m0_.country_member_2_id = :mpCountry
                AND m0_.related_rate_country_id %s :country
                AND m0_.nb_vote > :nbVote
                ORDER BY political_group.position, member_1.last_name
            ';

        $sql = sprintf($sql, null === $country ? 'IS' : '=');

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $results = $stmt->executeQuery([
            'mpCountry' => $mpCountry->getId(),
            'country' => $country?->getId(),
            'nbVote' => $minNbVote,
        ]);

        return $results->fetchAllAssociative();
    }

    /**
     * classic sql query is used to improve speed
     */
    public function compareByGroup(PoliticalGroup $group, ?Country $country, ?int $minNbVote = 5): array
    {
        $sql = 'SELECT * 
                FROM member_to_member_vote_comparison m0_ 
                JOIN member as member_1 on m0_.member_1_id = member_1.id
                WHERE m0_.group_member_1_id = :group
                AND m0_.group_member_2_id = :group
                AND m0_.related_rate_country_id %s :country
                AND m0_.nb_vote > :nbVote
                ORDER BY m0_.country_member_1_id, member_1.last_name
            ';

        $sql = sprintf($sql, null === $country ? 'IS' : '=');

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $results = $stmt->executeQuery([
            'group' => $group->getId(),
            'country' => $country?->getId(),
            'nbVote' => $minNbVote,
        ]);

        return $results->fetchAllAssociative();
    }
}
