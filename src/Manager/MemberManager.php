<?php

namespace App\Manager;

use App\Entity\Member;
use App\Entity\MemberVote;
use Doctrine\ORM\EntityManagerInterface;

class MemberManager
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function compare(Member $member, array $members, array $voteValues): array
    {
        $data = [];

        $memberVoteRepository = $this->em->getRepository(MemberVote::class);
        $memberVotes = $memberVoteRepository->findFeaturedVotesByMember($member, $voteValues);

        /** @var Member $item */
        foreach ($members as $item) {
            $votes = [];
            $vote2 = [];

            foreach ($memberVotes as $memberVote) {
                $votes[$memberVote->getVote()->getId()] = $memberVote;
            }

            foreach ($memberVoteRepository->findFeaturedVotesByMember($item, $voteValues, array_keys($votes)) as $memberVote) {
                $vote2[$memberVote->getVote()->getId()] = $memberVote;
            }

            $data[] = [
                'member' => $member,
                'memberCompared'   => $item,
                'data' => $this->compareArray($votes, $vote2),
            ];
        }

        return $data;
    }

    public function compareArray(array $array1, array $array2): array
    {
        $results = [
            'same' => 0,
            'same_detail' => [],
            'difference' => 0,
            'difference_detail' => [],
            'total' => 0
        ];

        foreach ($array1 as $key => $item) {
            if (false === isset($array2[$key])) {
                continue;
            }

            $mainMemberVote = $item;
            $comparedMemberVote = $array2[$key];

            if ($mainMemberVote->getValue() === $comparedMemberVote->getValue()) {
                $results['same'] += 1;
                $results['same_detail'][] = ['voteItem' => $item];
            } else {
                $results['difference'] += 1;
                $results['difference_detail'][] = [
                    'voteItem' => $item,
                    'mainMemberVoteValue' => $item->getValue(),
                    'comparedMemberVoteValue' => $comparedMemberVote->getValue()
                ];
            }

            $results['total'] += 1;
        }

        $results['rate']['same'] = $results['same'] / $results['total'] * 100;
        $results['rate']['difference'] = $results['difference'] / $results['total'] * 100;

        return $results;
    }
}
