<?php

namespace App\Normaliser\Chart;

class PieNormaliser
{
    public static function voteToPie(array $data): array
    {
        $labels = ['labels' => ['Vote similaire', 'Vote dofférent']];
        $dataset = [
            'datasets' =>[
                ['data' => [$data['same'], $data['difference']]],

        ]];


        return array_merge($labels, $dataset);
    }
}
