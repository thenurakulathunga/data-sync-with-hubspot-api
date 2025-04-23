<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ToastTrigger implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $message,
        public string $type = 'success',
        public int $duration = 5000
    ) {
        logger()->info('ToastTrigger fired', [
            'message' => $message,
            'type' => $type,
            'duration' => $duration,
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('ShowToast'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'duration' => $this->duration,
        ];
    }
}
