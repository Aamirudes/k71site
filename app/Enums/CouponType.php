<?php

namespace App\Enums;

enum CouponType: string
{
    case Percentage = 'percentage';
    case Flat = 'flat';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Percentage',
            self::Flat => 'Flat Amount',
        };
    }
}
