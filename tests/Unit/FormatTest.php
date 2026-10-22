<?php

namespace Tests\Unit;

use App\Support\Format;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    public function test_rupiah_memakai_titik_sebagai_pemisah_ribuan()
    {
        $this->assertSame('Rp 15.000', Format::rupiah(15000));
    }

    public function test_rupiah_nol()
    {
        $this->assertSame('Rp 0', Format::rupiah(0));
    }

    public function test_rupiah_dari_kolom_desimal_database()
    {
        $this->assertSame('Rp 1.500.000', Format::rupiah('1500000.00'));
    }

    public function test_tanggal_memakai_nama_bulan_indonesia()
    {
        $this->assertSame('5 Agt 2026, 14:07', Format::tanggal(Carbon::parse('2026-08-05 14:07:33')));
    }
}
