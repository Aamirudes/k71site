<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPayoutDueNotification extends Notification
{
    use Queueable;

    public Order $order;
    public array $payoutData;

    public function __construct(Order $order, array $payoutData = [])
    {
        $this->order = $order;
        $this->payoutData = $payoutData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $vendor = $this->order->vendor;
        $vendorName = $vendor->shop_name ?? 'Vendor';
        $upiId = $vendor->settings->upi_id ?? ($this->payoutData['vpa'] ?? 'Not Configured');

        $productSubtotal = max(0, $this->order->subtotal - $this->order->discount_amount);
        $platformFee = (int) round($productSubtotal * 0.05);
        $vendorPayout = $this->order->vendor_payout_amount ?: ($productSubtotal - $platformFee);

        $amountRupees = number_format($vendorPayout / 100, 2);
        $totalPaidByCustomer = number_format($this->order->total_amount / 100, 2);
        $feeRupees = number_format($platformFee / 100, 2);
        $shippingRupees = number_format(($this->order->shipping_amount ?? 0) / 100, 2);

        return (new MailMessage)
            ->subject("🔔 [Action Required] Payout Due: ₹{$amountRupees} for Order #{$this->order->order_number}")
            ->greeting("Hello Aamir,")
            ->line("Vendor **{$vendorName}** has accepted Order **#{$this->order->order_number}** and is packing the items for courier dispatch.")
            ->line("---")
            ->line("**💰 Payout Transfer Details:**")
            ->line("• **Vendor Shop:** {$vendorName}")
            ->line("• **Vendor UPI ID:** `{$upiId}`")
            ->line("• **Exact Amount to Pay (95% Net):** **₹{$amountRupees}**")
            ->line("---")
            ->line("**📊 Transaction Breakdown:**")
            ->line("• Customer Paid Total: ₹{$totalPaidByCustomer}")
            ->line("• Your 5% Platform Profit Kept: ₹{$feeRupees}")
            ->line("• Shipping Fee Retained: ₹{$shippingRupees}")
            ->action('View Order in Admin Dashboard', url('/admin/orders/' . $this->order->id))
            ->line("💡 **Next Step:** Open PhonePe or Google Pay on your phone, transfer **₹{$amountRupees}** to `{$upiId}`, and record the UTR in your Admin Dashboard.");
    }
}
