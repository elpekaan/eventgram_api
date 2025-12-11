<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'ticket' => [
                'id' => $this->ticket->id,
                'ticket_code' => $this->ticket->ticket_code,
                'event' => [
                    'id' => $this->ticket->event->id,
                    'name' => $this->ticket->event->name,
                ],
            ],
            'from_user' => [
                'id' => $this->seller->id,
                'name' => $this->seller->name,
            ],
            'to_user' => [
                'id' => $this->buyer->id,
                'name' => $this->buyer->name,
                'email' => $this->buyer->email,
            ],
            'asking_price' => $this->asking_price,
            'platform_commission' => $this->platform_commission,
            'seller_receives' => $this->seller_receives,
            'status' => $this->status,
            'escrow_status' => $this->escrow_status,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
        ];
    }
}
