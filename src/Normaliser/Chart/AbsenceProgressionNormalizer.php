<?php

namespace App\Normaliser\Chart;

use App\Entity\MemberVote;

class AbsenceProgressionNormalizer
{
    public function process(array $votes): array
    {
        $progression = [];
        foreach ($votes as $vote) {
            $progression[] = [
                'date' => $vote['vote_date']->format('Y-m-d'),
                'value' => MemberVote::VOTE_DID_NOT_VOTE === $vote['vote_value'] ? 1 : 0
            ];
        }

        return array_values(array_filter($progression));
    }
}
