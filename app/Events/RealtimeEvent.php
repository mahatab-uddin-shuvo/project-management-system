<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RealtimeEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $role;
    public $userId;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($role,$userId,$message)
    {
        $this->role = $role;
        $this->userId = $userId;
        $this->message = $message;

    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
//    public function broadcastOn(): array
//    {
//        return [
//            new Channel('realtime'),
////            new PrivateChannel('private-channel.user.'. $this->userId),
//        ];
//    }

    public function broadcastOn()
    {
        // TODO: Implement broadcastOn() method.
        return [
            new Channel('realtime'),
        ];
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
               "message" =>$this->message,
               "userId" =>$this->userId,
               "role" =>$this->role,
            ]
        ];
    }

}
