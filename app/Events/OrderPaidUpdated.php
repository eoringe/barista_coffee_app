<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaidUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $orderId
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('orders'); // public channel
    }

    /**
     * Data payload for listeners.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderPaidUpdated';
    }
}
