<?php

declare(strict_types=1);

namespace App\Modules\Auth\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'user' => [
                'id' => $this->resource->user->id,
                'name' => $this->resource->user->name,
                'email' => $this->resource->user->email,
                'avatar_url' => $this->resource->user->avatar_url,
                'role' => $this->resource->user->role,
            ],
            'token' => $this->resource->token,
        ];
    }
}
