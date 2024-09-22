<?php

namespace App\Manager;

use App\Entity\MemberVoteStatistic;
use App\Math\Stats;

class StatisticManager
{
    public function generateAbsencePrediction(array $membersStatistic): void
    {
        $attendance = $total = [];

        /** @var MemberVoteStatistic[] $membersStatistic */
        foreach ($membersStatistic as $memberStatistic) {
            $attendance[] = $memberStatistic->getAttendance();
            $total[] = $memberStatistic->getNbVote();
        }

        $cov = Stats::covariance($total, $attendance);
        $var = Stats::variance($total);
        $coeff = $cov / $var;

        foreach ($membersStatistic as $memberStatistic) {
            $memberStatistic->setAttendancePrediction($memberStatistic->getNbVote() * $coeff);
        }
    }
}
