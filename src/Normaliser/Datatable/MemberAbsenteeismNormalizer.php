<?php

namespace App\Normaliser\Datatable;

use App\Entity\MemberVoteStatistic;
use App\Math\Stats;

class MemberAbsenteeismNormalizer
{
    /**
     * @param MemberVoteStatistic[] $membersVoteStatistic
     */
    public function process(array $membersVoteStatistic): array
    {
        $data = [];

        foreach ($membersVoteStatistic as $memberVoteStatistic) {
            $data[] = [
                'member' => $memberVoteStatistic->getMember(),
                'total' => $memberVoteStatistic->getNbVote(),
                'miss' => $memberVoteStatistic->getMiss(),
                'attendance' => $memberVoteStatistic->getAttendance(),
                'ratio' => $memberVoteStatistic->getMissRatio(),
                'color' => '#' . $memberVoteStatistic->getMember()->getGroup()->getColor(),
                'prediction' => [
                    'value' => $memberVoteStatistic->getAttendancePrediction(),
                    'gap' => $memberVoteStatistic->getAttendanceGapWithPrediction(),
                    'ratio' => 0 === $memberVoteStatistic->getAttendancePrediction()
                        ? 0
                        : Stats::evolutionRate(
                            $memberVoteStatistic->getAttendancePrediction(),
                            $memberVoteStatistic->getAttendance()
                        )
                ],
            ];
        }

        return $data;
    }
}
