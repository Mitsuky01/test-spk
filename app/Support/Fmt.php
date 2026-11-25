<?php

namespace App\Support;

class Fmt
{
    public static function rp($n): string
    {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
    }

    public static function roman(int $m): string
    {
        return ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$m];
    }
}
