<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Support\Invoice;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    // dipakai rute pelanggan (middleware auth) dan rute admin (adminMiddle)
    public function __invoke(Pesanan $pesanan)
    {
        abort_unless(Auth::guard('adminMiddle')->check() || $pesanan->milik(Auth::user()), 403);

        $invoice = new Invoice($pesanan);

        return response($invoice->pdf(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$invoice->namaFile()}\"",
        ]);
    }
}
