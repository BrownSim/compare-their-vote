<?php

namespace App\Normaliser\Chart;

use Symfony\Contracts\Translation\TranslatorInterface;

class PieNormaliser
{
    public function __construct(
      private readonly TranslatorInterface $translator
    ) {
    }

    public function voteToPie(array $data): array
    {
        $labels = [
            'labels' => [
                $this->translator->trans(id: 'same_vote'),
                $this->translator->trans(id: 'different_vote'),
            ]
        ];
        $dataset = [
            'datasets' =>[
                ['data' => [$data['same'], $data['difference']]],

        ]];

        return array_merge($labels, $dataset);
    }
}
