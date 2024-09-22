<?php

namespace App\Math;

class Round
{
    public static function roundUpToAny(float $n, int $x = 5): int
    {
        return (ceil($n) % $x === 0) ? ceil($n) : round(($n + $x / 2) / $x) * $x;
    }
}
