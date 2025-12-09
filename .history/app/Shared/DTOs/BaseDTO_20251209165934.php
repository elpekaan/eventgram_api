<?php

declare(strict_types=1);

namespace App\Shared\DTOs;

use Illuminate\Http\Request;

abstract readonly class BaseDTO
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    // İleride request'ten otomatik DTO oluşturmak için
    // bunu kullanacağız.
    abstract public static function fromRequest(Request $request): static;
}
