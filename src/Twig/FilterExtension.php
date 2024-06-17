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
        ];
    }

    public function chartPieThis(array $data): string
    {
        return json_encode($this->pieNormaliser->voteToPie($data));
    }
}
