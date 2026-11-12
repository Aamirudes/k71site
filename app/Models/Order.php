<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'order_number',
        'order_type',
        'pickup_code',
        'item_count',
        'subtotal',
        'shipping_amount',
        'packaging_amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'food_fulfillment_stage',
        'payment_method',
        'payment_status',
        'address_snapshot',
        'tracking_number',
        'tracking_url',
        'courier_name',
        'idempotency_key',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'shipped_at',
        'delivered_at',
        'packed_at',
        'estimated_delivery_time',
        'kitchen_notes',
        'settlement_status',
        'settlement_expected_at',
        'settled_at',
        'settlement_batch_ref',
        'vendor_payout_status',
        'vendor_payout_amount',
        'vendor_payout_utr',
        'vendor_payout_at',
        'vendor_accepted_at',
        'accepted_at',
        'preparing_at',
        'ready_at',
        'out_for_delivery_at',
        'vendor_rejected_at',
        'vendor_rejection_reason',
    ];

    protected $casts = [
        'address_snapshot' => 'json',
        'packaging_amount' => 'integer',
        'cancelled_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'packed_at' => 'datetime',
        'estimated_delivery_time' => 'datetime',
        'settlement_expected_at' => 'datetime',
        'settled_at' => 'datetime',
        'vendor_payout_at' => 'datetime',
        'vendor_accepted_at' => 'datetime',
        'accepted_at' => 'datetime',
        'preparing_at' => 'datetime',
        'ready_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'vendor_rejected_at' => 'datetime',
    ];

    /**
     * Get shipping address (aliases to address_snapshot)
     */
    public function getShippingAddressAttribute()
    {
        return $this->address_snapshot;
    }

    /**
     * Check if this order is a food delivery or takeaway order.
     */
    public function isFoodOrder(): bool
    {
        return str_starts_with($this->order_type ?? '', 'food_');
    }

    /**
     * Check if this order is doorstep food delivery.
     */
    public function isFoodDelivery(): bool
    {
        return $this->order_type === 'food_delivery';
    }

    /**
     * Check if this order is takeaway/pickup.
     */
    public function isFoodTakeaway(): bool
    {
        return $this->order_type === 'food_takeaway';
    }

    public function canCustomerCancel(): bool
    {
        if (in_array($this->status, ['cancelled', 'shipped', 'out_for_delivery', 'delivered', 'returned', 'refunded', 'ready_for_pickup'], true)) {
            return false;
        }
        return true;
    }

    public function getFoodStageLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Order Placed',
            'processing' => 'Preparing in Kitchen',
            'ready_for_pickup' => $this->isFoodTakeaway() ? 'Ready for Pickup' : 'Ready for Dispatch',
            'shipped', 'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status ?? 'Pending')),
        };
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
    
    public function paymentClaim(): HasOne
    {
        return $this->hasOne(PaymentClaim::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->latest();
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class)->latestOfMany();
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function deliveryAssignment(): HasOne
    {
        return $this->hasOne(DeliveryAssignment::class)->latestOfMany();
    }

    public function deliveryAssignments(): HasMany
    {
        return $this->hasMany(DeliveryAssignment::class);
    }

    public function orderCommission(): HasOne
    {
        return $this->hasOne(OrderCommission::class, 'transaction_id')
            ->where('transaction_type', 'order')
            ->where('event_type', 'charge');
    }

    public function orderCommissions(): HasMany
    {
        return $this->hasMany(OrderCommission::class, 'transaction_id')
            ->where('transaction_type', 'order');
    }
}
