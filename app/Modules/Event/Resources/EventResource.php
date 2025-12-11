<?php

declare(strict_types=1);

namespace App\Modules\Event\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'date' => $this->date,
            'doors_open' => $this->doors_open,
            'ends_at' => $this->ends_at,
            'venue' => [
                'id' => $this->venue->id,
                'name' => $this->venue->name,
                'city' => $this->venue->city,
            ],
            'poster_url' => $this->poster_url,
            'banner_url' => $this->banner_url,
            'status' => $this->status,
            'total_capacity' => $this->total_capacity,
            'tickets_sold' => $this->tickets_sold,
            'available_tickets' => $this->available_tickets,
            'ticket_types' => $this->ticketTypes->map(fn($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'price' => $type->price,
                'quantity' => $type->quantity,
                'available' => $type->available,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
