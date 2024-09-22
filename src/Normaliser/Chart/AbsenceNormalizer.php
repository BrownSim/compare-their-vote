<?php

namespace App\Normaliser\Chart;

use App\Entity\MemberVoteStatistic;
use App\Math\Stats;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class AbsenceNormalizer
{
    use Tooltip;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Environment $twig
    ) {
    }

    /**
     * @param MemberVoteStatistic[] $membersVoteStatistic
     */
    public function memberVoteStatsToDatatable(array $membersVoteStatistic): array
    {
        $data = [
            'label' => [
                'x' => $this->translator->trans('absenteeism.chart.anomaly.x'),
                'y' => $this->translator->trans('absenteeism.chart.anomaly.y'),
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

    public function analyseAbsenceByMpAndPoliticalGroup(array $membersStatistic): array
    {
        $data = [];
        $nbVoteByGroup = [];
        $nbMissByGroup = [];

        /** @var MemberVoteStatistic $memberStatistic */
        foreach ($membersStatistic as $memberStatistic) {
            $member = $memberStatistic->getMember();
            $group = $member->getGroup()->getId();

            if (!isset($nbVoteByGroup[$group])) {
                $nbVoteByGroup[$group] = 0;
                $nbMissByGroup[$group] = 0;
            }

            $nbVoteByGroup[$group] += $memberStatistic->getNbVote();
            $nbMissByGroup[$group] += $memberStatistic->getMiss();

            $data[$group]['category'] = ['name' => $member->getGroup()->getShortLabel()];

            $data[$group]['data'][] = [
                'name' => $member->getFirstName() . ' ' . $member->getLastName(),
                'value' => $memberStatistic->getMissRatio(),
                'color' => $member->getGroup()->getColor(),
                'tooltip' => $this->generateGraphToolTip($memberStatistic)
            ];
        }

        foreach ($data as $groupIp => $datum) {
            $data[$groupIp]['category']['value'] = ($nbMissByGroup[$groupIp] * 100) / $nbVoteByGroup[$groupIp];
        }

        //reset array key to prevent json_encore preserve array key
        $data = array_values(array_filter($data));

        return $data;
    }
}
