<?php

namespace App\Enums;

enum OrderStatus: string
{
    case AwaitingPayment = 'awaiting_payment';
    case PaymentSubmitted = 'payment_submitted';
    case PaymentVerified = 'payment_verified';
    case Processing = 'processing';
    case Packed = 'packed';
    case Shipped = 'shipped';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case PaymentFailed = 'payment_failed';
    case PaymentRejected = 'payment_rejected';
    case ReturnRequested = 'return_requested';
    case ReturnApproved = 'return_approved';
    case Returned = 'returned';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingPayment => 'Awaiting Payment',
            self::PaymentSubmitted => 'Payment Submitted',
            self::PaymentVerified => 'Payment Verified',
            self::Processing => 'Processing',
            self::Packed => 'Packed',
            self::Shipped => 'Shipped',
            self::OutForDelivery => 'Out for Delivery',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::PaymentFailed => 'Payment Failed',
            self::PaymentRejected => 'Payment Rejected',
            self::ReturnRequested => 'Return Requested',
            self::ReturnApproved => 'Return Approved',
            self::Returned => 'Returned',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AwaitingPayment => 'warning',
            self::PaymentSubmitted => 'info',
            self::PaymentVerified, self::Processing => 'primary',
            self::Packed => 'info',
            self::Shipped, self::OutForDelivery => 'info',
            self::Delivered => 'success',
            self::Cancelled, self::PaymentFailed, self::PaymentRejected => 'danger',
            self::ReturnRequested, self::ReturnApproved => 'warning',
            self::Returned, self::Refunded => 'secondary',
        };
    }

    /**
     * Returns valid transitions from this status.
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::AwaitingPayment => [self::PaymentSubmitted, self::Cancelled, self::PaymentFailed],
            self::PaymentSubmitted => [self::PaymentVerified, self::PaymentRejected, self::Cancelled],
            self::PaymentVerified => [self::Processing, self::Cancelled],
            self::Processing => [self::Packed, self::Cancelled],
            self::Packed => [self::Shipped, self::Cancelled],
            self::Shipped => [self::OutForDelivery, self::Delivered],
            self::OutForDelivery => [self::Delivered],
            self::Delivered => [self::ReturnRequested],
            self::ReturnRequested => [self::ReturnApproved, self::Delivered], // denied return → back to delivered
            self::ReturnApproved => [self::Returned],
            self::Returned => [self::Refunded],
            default => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::Delivered,
            self::Cancelled,
            self::PaymentFailed,
            self::Refunded,
        ], true);
    }

    public function isActive(): bool
    {
        return !$this->isFinal() && $this !== self::PaymentRejected;
    }

    /**
     * Whether the customer can cancel at this status.
     */
    public function customerCanCancel(): bool
    {
        return in_array($this, [
            self::AwaitingPayment,
            self::PaymentSubmitted,
            self::PaymentVerified,
            self::Processing,
        ], true);
    }
}
