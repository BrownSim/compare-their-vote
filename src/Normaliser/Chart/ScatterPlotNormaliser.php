<?php

namespace App\Normaliser\Chart;

use App\Entity\MemberVoteStatistic;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ScatterPlotNormaliser
{
    use Tooltip;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Environment $twig
    ) {
    }

    public function absenceToScatterPlot(array $data): array
    {
        $scatterPlotData = [];
        foreach ($data as $datum) {
            $scatterPlotData[] = [
                'x' => $datum['attendance'],
                'y' => $datum['total'],
                'color' => '#' . $datum['member']['member']->getGroup()->getColor(),
                'value' => 10,
            ];
        }

        return $scatterPlotData;
    }

    /**
     * @param MemberVoteStatistic[] $membersVoteStatistic
     */
    public function absenceDotPlot(array $membersVoteStatistic): array
    {
        $data = [];
        foreach ($membersVoteStatistic as $memberVoteStatistic) {
            $member = $memberVoteStatistic->getMember();
            $data[] = [
                'member' => $member->getFirstName() . ' ' . $member->getLastName(),
                'color' => '#' . $member->getGroup()->getColor(),
                'ratio' => round($memberVoteStatistic->getMissRatio()),
                'total' => $memberVoteStatistic->getNbVote(),
                'tooltip' => $this->generateGraphToolTip($memberVoteStatistic),
            ];
        }

        return $data;
    }
}
