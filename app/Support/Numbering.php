<?php

namespace App\Support;

use App\Models\Ph;
use App\Models\Project;
use App\Models\Spk;
use Carbon\Carbon;

class Numbering
{
    /** 0117/PMD/26 : urut per tahun */
    public static function project(): string
    {
        $max = Project::whereYear('created_at', now()->year)
            ->pluck('number')->map(fn ($n) => (int) $n)->max() ?? 0;

        return sprintf('%04d/PMD/%s', $max + 1, now()->format('y'));
    }

    /** 007/XI/PH-PMD/26 : urut per bulan */
    public static function ph(?Carbon $date = null): string
    {
        return self::monthly(Ph::class, 'PH', $date);
    }

    /** 001/XI/SPK-PMD/26 : urut per bulan */
    public static function spk(?Carbon $date = null): string
    {
        return self::monthly(Spk::class, 'SPK', $date);
    }

    private static function monthly(string $model, string $code, ?Carbon $date): string
    {
        $date ??= now();
        $suffix = '/' . Fmt::roman($date->month) . "/{$code}-PMD/" . $date->format('y');

        $max = $model::where('number', 'like', '%' . $suffix)
            ->pluck('number')->map(fn ($n) => (int) $n)->max() ?? 0;

        return sprintf('%03d%s', $max + 1, $suffix);
    }
}
