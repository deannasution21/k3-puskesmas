<?php

namespace App\Support;

use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Periode
{
    public static function bulanSekarang(): int
    {
        return (int) now()->format('n');
    }

    public static function tahunSekarang(): int
    {
        return (int) now()->format('Y');
    }

    /**
     * Parse string periode format "YYYY-MM" menjadi [bulan, tahun].
     *
     * @return array{0: int, 1: int}
     */
    public static function parse(string $periode): array
    {
        if (! preg_match('/^(\d{4})-(\d{2})$/', $periode, $matches)) {
            throw new NotFoundHttpException('Format periode tidak valid. Gunakan format YYYY-MM.');
        }

        return [(int) $matches[2], (int) $matches[1]];
    }

    public static function format(int $bulan, int $tahun): string
    {
        return Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
    }
}
