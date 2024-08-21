<?php

namespace App\Twig;

use App\Normaliser\Chart\PieNormaliser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FilterExtension extends AbstractExtension
{
    public function __construct(
        private readonly PieNormaliser $pieNormaliser
    ) {
    }

    public function getFilters()
    {
        return [
            new TwigFilter('chart_pie_this', [$this, 'chartPieThis']),
            new TwigFilter('round_down_5', [$this, 'roundDown5']),
        ];
    }

    public function chartPieThis(array $data): string
    {
        return json_encode($this->pieNormaliser->voteToPie($data));
    }

    public function roundDown5(int $x): int
    {
        return floor($x / 5) * 5;
    }
}
