<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorThemeRequest extends Model
{
    protected $table = 'vendor_theme_requests';

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_PAYMENT_AWAITING = 'payment_awaiting_confirmation';
    public const STATUS_PAYMENT_CONFIRMED = 'payment_confirmed';
    public const STATUS_ACCESS_GRANTED = 'access_granted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ACCESS_REVOKED = 'access_revoked';

    public const ACTIVE_PENDING_STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_PENDING_REVIEW,
        self::STATUS_PAYMENT_AWAITING,
        self::STATUS_PAYMENT_CONFIRMED,
    ];

    public const PAYMENT_UNVERIFIED = 'unverified';
    public const PAYMENT_AWAITING = 'awaiting_confirmation';
    public const PAYMENT_CONFIRMED = 'confirmed';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'reference_code',
        'vendor_id',
        'theme_id',
        'price_snapshot_paise',
        'currency',
        'status',
        'payment_verification_status',
        'payment_verified_by',
        'payment_verified_at',
        'payment_reference',
        'payment_notes',
        'vendor_note',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'status_history',
    ];

    protected $casts = [
        'price_snapshot_paise' => 'integer',
        'payment_verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'status_history' => 'array',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(StorefrontTheme::class, 'theme_id', 'theme_id');
    }

    public function paymentVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return in_array($this->status, self::ACTIVE_PENDING_STATUSES, true);
    }

    public function isPaymentConfirmed(): bool
    {
        return $this->payment_verification_status === self::PAYMENT_CONFIRMED;
    }

    public function getFormattedPriceSnapshotAttribute(): string
    {
        if ($this->price_snapshot_paise === null) {
            return 'Price on Request';
        }
        return '₹' . number_format($this->price_snapshot_paise / 100, $this->price_snapshot_paise % 100 === 0 ? 0 : 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_REQUESTED => 'Requested',
            self::STATUS_PENDING_REVIEW => 'Pending Review',
            self::STATUS_PAYMENT_AWAITING => 'Payment Awaiting Confirmation',
            self::STATUS_PAYMENT_CONFIRMED => 'Payment Confirmed',
            self::STATUS_ACCESS_GRANTED => 'Access Granted',
            self::STATUS_DECLINED => 'Declined',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_ACCESS_REVOKED => 'Access Revoked',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function hasPriceChangedSinceRequest(): bool
    {
        $theme = $this->theme;
        if (!$theme) {
            return false;
        }
        return $theme->price_paise !== $this->price_snapshot_paise;
    }
}
