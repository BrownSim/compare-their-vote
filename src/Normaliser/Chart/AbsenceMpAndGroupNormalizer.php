<?php

namespace App\Normaliser\Chart;

use App\Entity\MemberVoteStatistic;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class AbsenceMpAndGroupNormalizer
{
    use Tooltip;

    public function __construct(
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function process(array $membersStatistic): array
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
