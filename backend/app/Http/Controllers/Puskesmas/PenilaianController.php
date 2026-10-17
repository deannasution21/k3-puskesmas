<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use App\Support\Penilaian;
use App\Support\Periode;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    public function current(Request $request)
    {
        $bulan = Periode::bulanSekarang();
        $tahun = Periode::tahunSekarang();

        return response()->json(array_merge(
            ['periode_bulan' => $bulan, 'periode_tahun' => $tahun],
            Penilaian::untukPuskesmas($request->user()->puskesmas_id, $bulan, $tahun),
        ));
    }
}
