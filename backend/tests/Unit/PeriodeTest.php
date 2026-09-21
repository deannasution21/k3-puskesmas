<?php

namespace Tests\Unit;

use App\Support\Periode;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PeriodeTest extends TestCase
{
    public function test_parse_extracts_month_and_year(): void
    {
        [$bulan, $tahun] = Periode::parse('2026-09');

        $this->assertSame(9, $bulan);
        $this->assertSame(2026, $tahun);
    }

    public function test_parse_rejects_invalid_format(): void
    {
        $this->expectException(NotFoundHttpException::class);

        Periode::parse('September-2026');
    }

    public function test_format_returns_indonesian_month_name(): void
    {
        $this->assertSame('Mei 2026', Periode::format(5, 2026));
        $this->assertSame('Agustus 2026', Periode::format(8, 2026));
    }
}
