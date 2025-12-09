<?php

declare(strict_types=1);

namespace App\Shared\DTOs;

use Illuminate\Foundation\Http\FormRequest;

abstract readonly class BaseDTO
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    // Request yerine FormRequest yaptık. Artık çocuklar bununla uyumlu olacak.
    abstract public static function fromRequest(FormRequest $request): static;
}
