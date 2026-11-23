<?php

namespace Tests\Unit;

use App\Console\Commands\SyncChartFromSheets;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SheetNumberParseTest extends TestCase
{
    public static function cells(): array
    {
        return [
            // Format sheet (Amerika): koma ribuan, titik desimal.
            'denda' => ['Rp500,000,000', 500000000.0],
            'denda miliar' => ['Rp1,500,000,000', 1500000000.0],
            'biaya perkara' => ['Rp5,000', 5000.0],
            'nol' => ['Rp0', 0.0],
            'penjara desimal' => ['0.67', 0.67],
            'penjara' => ['12', 12.0],
            'ribuan dan desimal' => ['1,234.56', 1234.56],
            // Format Indonesia tetap dikenali.
            'ribuan Indonesia' => ['Rp1.500.000', 1500000.0],
            'desimal Indonesia' => ['0,5', 0.5],
            'ribuan & desimal Indonesia' => ['1.500.000,50', 1500000.5],
            // Dua angka dalam satu sel: angka pertama.
            'dua angka' => ['3 & 4', 3.0],
        ];
    }

    #[DataProvider('cells')]
    public function test_parses_sheet_numbers(string $cell, float $expected): void
    {
        $this->assertSame($expected, SyncChartFromSheets::parseNumber($cell));
    }

    public function test_cell_without_digits_is_null(): void
    {
        $this->assertNull(SyncChartFromSheets::parseNumber('-'));
    }
}
