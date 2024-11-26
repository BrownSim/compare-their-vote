<?php

namespace App\Normaliser\Chart;

use Symfony\Contracts\Translation\TranslatorInterface;

class PieMpComparisonNormalizer
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function process(array $data): array
    {
        return [
            'dataset' => [
                [
                    'category' => $this->translator->trans(id: ''),
                    'value' => $data['same'],
                ], [
                    'category' => $this->translator->trans(id: 'different_vote'),
                    'value' => $data['difference'],
                ]]
        ];
    }
}
