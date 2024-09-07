<?php

namespace App\Normaliser\Chart;

use App\Entity\MemberVoteStatistic;

trait Tooltip
{
    private function generateGraphToolTip(MemberVoteStatistic $memberVoteStatistic): string
    {
        return $this->twig->render('common/chart/_tooltip.html.twig', [
            'memberVoteStatistic' => $memberVoteStatistic,
        ]);
    }
}
