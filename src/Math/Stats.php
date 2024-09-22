<?php

namespace App\Math;

class Stats
{
    public static function variance(array $data, ?int $round = null): float
    {
        $num_of_elements = count($data);
        if ($num_of_elements <= 1) {
            throw new \Error('The data size must be greater than 1.');
        }

        $sumSquareDifferences = 0.0;
        $average = self::mean($data);

        foreach ($data as $i) {
            // sum of squares of differences between
            // all numbers and means.
            $sumSquareDifferences += ($i - $average) ** 2;
        }

        return self::round($sumSquareDifferences / ($num_of_elements - 1), $round);
    }

    public static function covariance(array $x, array $y): false|float
    {
        $countX = count($x);
        $countY = count($y);
        if ($countX !== $countY) {
            throw new \Error(
                'Covariance requires that both inputs have same number of data points.'
            );
        }
        if ($countX < 2) {
            throw new \Error(
                'Covariance requires at least two data points.'
            );
        }
        $meanX = self::mean($x);
        $meanY = self::mean($y);
        $add = 0.0;

        for ($pos = 0; $pos < $countX; $pos++) {
            $valueX = $x[$pos];
            if (!is_numeric($valueX)) {
                throw new \Error(
                    'Covariance requires numeric data points.'
                );
            }
            $valueY = $y[$pos];
            if (!is_numeric($valueY)) {
                throw new \Error(
                    'Covariance requires numeric data points.'
                );
            }
            $diffX = $valueX - $meanX;
            $diffY = $valueY - $meanY;
            $add += ($diffX * $diffY);
        }

        // covariance for sample: N - 1
        return $add / (float) ($countX - 1);
    }

    public static function rate(float|int $value1, float|int $value2): float|int
    {
        return $value1 * 100 / $value2;
    }

    public static function evolutionRate(float|int $value1, float|int $value2): float|int
    {
        return (($value2 - $value1) / $value1) * 100;
    }

    public static function mean(array $data): int|float|null
    {
        if (count($data) === 0) {
            throw new \Error('The data must not be empty.');
        }

        $sum = array_sum($data);

        return $sum / count($data);
    }

    public static function round(float $value, ?int $round): float
    {
        return is_null($round) ? $value : round($value, $round);
    }
}
