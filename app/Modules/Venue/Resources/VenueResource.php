<?php

declare(strict_types=1);

namespace App\Modules\Venue\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'city' => $this->city,
            'address' => $this->address,
            'capacity' => $this->capacity,
            'phone' => $this->phone,
            'website' => $this->website,
            'logo_url' => $this->logo_url,
            'status' => $this->status,
            'total_events' => $this->total_events,
            'total_followers' => $this->total_followers,
            'created_at' => $this->created_at,
        ];
    }
}
