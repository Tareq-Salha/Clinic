<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OutPatient implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $msg;
    protected $scretary_id;
    protected $appointment_id;
    protected $patientName;
    protected $doctorName;
    public function __construct($msg, $scretary_id, $appointment_id, $patientName, $doctorName)
    {
        $this->msg = $msg;
        $this->scretary_id = $scretary_id;
        $this->appointment_id = $appointment_id;
        $this->patientName = $patientName;
        $this->doctorName = $doctorName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('out-patient.' . $this->scretary_id),
        ];
    }
    public function broadcastWith()
    {
        return [
            'scretary_id' => $this->scretary_id,
            'message' => $this->msg,
            'appointment_id' => $this->appointment_id,
            'patientName' => $this->patientName,
            'doctorName' => $this->doctorName,
            'timestamp' => now()->toDateTimeString()
        ];
    }
    public function broadcastAs(): string
    {
        return 'patient.outed';
    }
}
