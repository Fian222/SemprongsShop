<?php

namespace App\Http\Controllers;

use App\Support\EksporPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EksporPesananController extends Controller
{
    public function __invoke(Request $request)
    {
        $rentang = $request->validate(...EksporPesanan::aturan());

        $ekspor = new EksporPesanan(Carbon::parse($rentang['dari']), Carbon::parse($rentang['sampai']));

        return response()->streamDownload(function () use ($ekspor) {
            $ekspor->tulis(fopen('php://output', 'w'));
        }, $ekspor->namaFile(), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
