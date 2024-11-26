<?php

namespace App\Normaliser\Datatable;

use App\Entity\MemberVoteStatistic;
use App\Math\Round;
use App\Math\Stats;

class CountryAbsenteeismNormalizer
{
    private array $mapColors = [
        '#e8f4ff',
        '#cee0ef',
        '#b4cde0',
        '#9bb9d0',
        '#81a5c0',
        '#6792b1',
        '#4d7ea1',
        '#346a91',
        '#1a5782',
        '#004372',
    ];

    /**
     * @param MemberVoteStatistic[] $membersVoteStatistic
     */
    public function process(array $membersVoteStatistic): array
    {
        $countryData = [];

        foreach ($membersVoteStatistic as $memberVoteStatistic) {
            $country = $memberVoteStatistic->getMember()->getCountry();

            if (isset($countryData[$country->getId()])) {
                $countryData[$country->getId()]['total'] += $memberVoteStatistic->getNbVote();
                $countryData[$country->getId()]['miss'] += $memberVoteStatistic->getMiss();
                continue;
            }

            $countryData[$country->getId()]['country'] = $country->getLabel();
            $countryData[$country->getId()]['id'] = $country->getIsoAlpha();
            $countryData[$country->getId()]['miss'] = $memberVoteStatistic->getMiss();
            $countryData[$country->getId()]['total'] = $memberVoteStatistic->getNbVote();
        }

        foreach ($countryData as &$countryDatum) {
            $countryDatum['ratio'] = Stats::rate($countryDatum['miss'], $countryDatum['total']);
        }

        unset($countryDatum);

        $min = min(array_column($countryData, 'ratio'));
        $max = max(array_column($countryData, 'ratio'));

        $diff = $max - $min;
        $steps = ceil($diff / count($this->mapColors));

        foreach ($countryData as &$countryDatum) {
            $ratio = Stats::rate($countryDatum['miss'], $countryDatum['total']);

            $countryDatum['color'] = $this->mapColors[(int) (Round::roundUpToAny($ratio, $steps) - $min) / $steps];
            $countryDatum['tooltip'] = 'Country: '
                . $countryDatum['country']
                . '<br/> Absenteeism rate: '
                . round($ratio, 2)
                . '%';
        }

        unset($countryDatum);

        //reset array key to prevent json_encore preserve array key
        $countryData = array_values(array_filter($countryData));

        return $countryData;
    }
}
