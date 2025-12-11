<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CheckInResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'check_in' => [
                'id' => $this->resource->checkIn->id,
                'checked_in_at' => $this->resource->checkIn->checked_in_at,
                'location_verified' => $this->resource->locationVerified,
                'was_late' => $this->resource->wasLate,
            ],
            'ticket' => [
                'id' => $this->resource->ticket->id,
                'ticket_code' => $this->resource->ticket->ticket_code,
                'type' => $this->resource->ticket->ticketType->name,
            ],
            'attendee' => [
                'id' => $this->resource->ticket->user->id,
                'name' => $this->resource->ticket->user->name,
            ],
            'event' => [
                'id' => $this->resource->ticket->event->id,
                'name' => $this->resource->ticket->event->name,
            ],
            'message' => $this->resource->message,
        ];
    }
}
