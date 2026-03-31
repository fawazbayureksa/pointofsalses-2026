<?php

namespace Tests\Unit\Models;

use App\Models\StockMovement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StockMovementTest extends TestCase
{
    #[DataProvider('typeLabelProvider')]
    public function test_get_type_label_attribute(string $type, string $expected): void
    {
        $movement       = new StockMovement();
        $movement->type = $type;

        $this->assertSame($expected, $movement->type_label);
    }

    public static function typeLabelProvider(): array
    {
        return [
            'adjustment'   => ['adjustment', 'Adjustment'],
            'sale'         => ['sale', 'Sale'],
            'return'       => ['return', 'Return'],
            'transfer_in'  => ['transfer_in', 'Transfer In'],
            'transfer_out' => ['transfer_out', 'Transfer Out'],
            'unknown'      => ['custom_type', 'Custom_type'],
        ];
    }

    #[DataProvider('typeBadgeClassProvider')]
    public function test_get_type_badge_class_attribute(string $type, string $expectedClass): void
    {
        $movement       = new StockMovement();
        $movement->type = $type;

        $this->assertSame($expectedClass, $movement->type_badge_class);
    }

    public static function typeBadgeClassProvider(): array
    {
        return [
            'sale'         => ['sale', 'bg-red-100 text-red-800'],
            'transfer_out' => ['transfer_out', 'bg-red-100 text-red-800'],
            'return'       => ['return', 'bg-green-100 text-green-800'],
            'transfer_in'  => ['transfer_in', 'bg-green-100 text-green-800'],
            'adjustment'   => ['adjustment', 'bg-blue-100 text-blue-800'],
            'unknown'      => ['other', 'bg-blue-100 text-blue-800'],
        ];
    }
}
