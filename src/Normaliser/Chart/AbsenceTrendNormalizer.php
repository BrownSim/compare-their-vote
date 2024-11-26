<?php

namespace App\Normaliser\Chart;

use App\Entity\MemberVoteStatistic;
use App\Math\Stats;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class AbsenceTrendNormalizer
{
    use Tooltip;

    public function __construct(
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @param MemberVoteStatistic[] $membersVoteStatistic
     */
    public function process(array $membersVoteStatistic): array
    {
        $data = [
            'label' => [
                'x' => $this->translator->trans('global.chart.anomaly.x'),
                'y' => $this->translator->trans('global.chart.anomaly.y'),
            ]
        ];

        foreach ($membersVoteStatistic as $memberVoteStatistic) {
            $data['data'][] = [
                'member' => $memberVoteStatistic->getMember()->getFirstName() . ' ' . $memberVoteStatistic->getMember()->getLastName(),
                'total' => $memberVoteStatistic->getNbVote(),
                'miss' => $memberVoteStatistic->getMiss(),
                'attendance' => $memberVoteStatistic->getAttendance(),
                'ratio' => $memberVoteStatistic->getMissRatio(),
                'color' => '#' . $memberVoteStatistic->getMember()->getGroup()->getColor(),
                'tooltip' => $this->generateGraphToolTip($memberVoteStatistic),
                'prediction' => [
                    'value' => $memberVoteStatistic->getAttendancePrediction(),
                    'gap' => $memberVoteStatistic->getAttendanceGapWithPrediction(),
                    'ratio' => 0 === $memberVoteStatistic->getAttendancePrediction()
                        ? 0
                        : Stats::evolutionRate($memberVoteStatistic->getAttendancePrediction(), $memberVoteStatistic->getAttendance())
                ],
            ];
        }

        return $data;
    }
}
