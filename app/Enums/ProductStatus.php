<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
    case PendingReview = 'pending_review';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::PendingReview => 'Pending Review',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::Active => 'success',
            self::Inactive => 'warning',
            self::PendingReview => 'info',
        };
    }

    public function isVisible(): bool
    {
        return $this === self::Active;
    }
}
