<?php

declare(strict_types=1);

namespace App\Modules\Order\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'event' => [
                'id' => $this->event->id,
                'name' => $this->event->name,
                'date' => $this->event->date,
                'venue' => $this->event->venue->name,
            ],
            'subtotal' => $this->subtotal,
            'service_fee' => $this->service_fee,
            'total' => $this->total,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
        ];
    }
}
