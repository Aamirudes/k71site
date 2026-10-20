<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $status = ucfirst($this->order->status);
        $vendorName = $this->order->vendor->shop_name;
        
        $message = (new MailMessage)
                    ->subject("Order Update - {$status} from {$vendorName}")
                    ->greeting("Hello {$notifiable->first_name},")
                    ->line("Your order #{$this->order->order_number} status has been updated to: **{$status}**.");

        if ($this->order->status === 'cancelled' && $this->order->cancel_reason) {
            $message->line("Reason: {$this->order->cancel_reason}");
        }
                    
        if ($this->order->tracking_url) {
            $message->action('Track Package', $this->order->tracking_url)
                    ->line("Tracking Number: {$this->order->tracking_number}");
        } else {
            $message->action('View Order', url('/orders'));
        }

        return $message->line('Thank you for shopping with independent brands!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
        ];
    }
}
