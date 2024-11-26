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
        return [
            'dataset' => [[
                'category' => $this->translator->trans(id: 'page.member_comparison.same_vote'),
                'value' => $data['same'],
            ], [
                'category' => $this->translator->trans(id: 'page.member_comparison.different_vote'),
                'value' => $data['difference'],
            ]]
        ];
    }
}
